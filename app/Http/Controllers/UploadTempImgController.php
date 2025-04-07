<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TemporaryImage;
use App\Services\ContentModerationService;
use Aws\Exception\AwsException;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class UploadTempImgController extends Controller
{
    protected $contentModerationService;

    public function __construct(ContentModerationService $contentModerationService)
    {
        $this->contentModerationService = $contentModerationService;
    }

    public function uploadTempImg(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = $image->getClientOriginalName();
            $uniqueFileName = uniqid().'_'.$fileName;
            $folder = uniqid('image-', true);

            // Create image manager instance
            $manager = new ImageManager(new Driver);

            try {
                // Debug log for AWS credentials
                Log::debug('AWS Credentials Check', [
                    'region' => env('AWS_DEFAULT_REGION'),
                    'key_exists' => ! empty(env('AWS_ACCESS_KEY_ID')),
                    'secret_exists' => ! empty(env('AWS_SECRET_ACCESS_KEY')),
                ]);

                // Create image instance
                $img = $manager->read($image);

                // Get original dimensions
                $originalWidth = $img->width();
                $originalHeight = $img->height();

                // Calculate quality and resize based on dimensions
                if ($originalWidth >= 3840 || $originalHeight >= 3840) {
                    // 4K images - aggressive compression
                    $quality = 40;
                    $img->scale(width: 2560); // Scale down to 2K
                } elseif ($originalWidth >= 2560 || $originalHeight >= 2560) {
                    // 2K images - moderate compression
                    $quality = 50;
                    $img->scale(width: 1920); // Scale down to 1080p
                } elseif ($originalWidth >= 1920 || $originalHeight >= 1920) {
                    // 1080p images - light compression
                    $quality = 60;
                    $img->scale(width: 1920);
                } else {
                    // Smaller images - minimal compression
                    $quality = 75;
                }

                // Convert image to JPEG binary for Rekognition
                $imageBytes = $img->toJpeg($quality)->toString();

                // Initialize Rekognition client
                $rekognition = new RekognitionClient([
                    'version' => 'latest',
                    'region' => config('services.ses.region'),
                    'credentials' => [
                        'key' => config('services.ses.key'),
                        'secret' => config('services.ses.secret'),
                    ],
                ]);

                // Step 1: Get image labels and check against categories
                $labelResult = $rekognition->detectLabels([
                    'Image' => [
                        'Bytes' => $imageBytes,
                    ],
                    'MaxLabels' => 20,
                    'MinConfidence' => 50,  // Lowered confidence threshold
                ]);

                $imageLabels = collect($labelResult['Labels'] ?? [])->pluck('Name')->map(fn ($label) => strtolower($label));

                // Log detected labels for debugging
                Log::info('Detected image labels:', ['labels' => $imageLabels->toArray()]);

                // Get all categories
                $categories = Category::all();

                // Category-specific keywords mapping
                $categoryKeywords = [
                    'Electronics' => ['device', 'electronic', 'gadget', 'computer', 'laptop', 'phone', 'cable', 'charger', 'adapter', 'tool', 'equipment', 'hardware'],
                    'Printed Materials' => ['book', 'paper', 'document', 'print', 'textbook', 'magazine', 'publication', 'notebook'],
                    'Stationeries' => ['pen', 'pencil', 'paper', 'notebook', 'stationery', 'office supply', 'school supply', 'tool'],
                    'Uniforms' => ['clothing', 'dress', 'uniform', 'apparel', 'garment', 'outfit', 'wear', 'cloth', 'fashion'],
                    'Medical Supplies' => ['medical', 'health', 'medicine', 'equipment', 'supply', 'tool', 'device'],
                    'Culinary Supplies' => ['kitchen', 'cooking', 'culinary', 'utensil', 'tool', 'equipment', 'appliance'],
                ];

                // Check if image matches any category or its keywords
                $matchesCategory = false;
                $matchedCategory = null;

                foreach ($categories as $category) {
                    $categoryName = strtolower($category->category);
                    // Check direct category match
                    if ($imageLabels->contains($categoryName)) {
                        $matchesCategory = true;
                        $matchedCategory = $category->category;
                        break;
                    }

                    // Check category keywords
                    $keywords = $categoryKeywords[$category->category] ?? [];
                    if ($imageLabels->intersect($keywords)->isNotEmpty()) {
                        $matchesCategory = true;
                        $matchedCategory = $category->category;
                        break;
                    }
                }

                Log::info('Category matching result:', [
                    'matches_category' => $matchesCategory,
                    'matched_category' => $matchedCategory,
                ]);

                if (! $matchesCategory) {
                    // Step 2: Check for inappropriate content if doesn't match categories
                    $moderationResult = $rekognition->detectModerationLabels([
                        'Image' => [
                            'Bytes' => $imageBytes,
                        ],
                        'MinConfidence' => 60,
                    ]);

                    $inappropriate = false;
                    foreach ($moderationResult['ModerationLabels'] ?? [] as $label) {
                        $restrictedCategories = [
                            'Explicit Nudity',
                            'Violence',
                            'Visually Disturbing',
                            'Hate Symbols',
                            'Drugs',
                            'Weapons',
                        ];

                        if (in_array($label['ParentName'], $restrictedCategories)) {
                            return response()->json([
                                'message' => 'This image contains inappropriate content and cannot be uploaded.',
                            ], 400);
                        }
                    }

                    // Step 3: Check if university-related
                    $universityKeywords = [
                        // Academic items
                        'book', 'textbook', 'notebook', 'calculator', 'computer',
                        'laptop', 'tablet', 'paper', 'document', 'folder',

                        // Equipment and tools
                        'tool', 'equipment', 'device', 'hardware', 'instrument',
                        'crimping tool', 'crimper', 'plier', 'tester', 'meter',
                        'electronic', 'cable', 'wire', 'connector', 'adapter',

                        // Clothing and accessories
                        'uniform', 'clothing', 'dress', 'outfit', 'apparel',
                        'garment', 'wear', 'bag', 'backpack', 'accessory',

                        // Locations and context
                        'laboratory', 'classroom', 'school', 'university',
                        'academic', 'education', 'student', 'study', 'learning',

                        // General supplies
                        'stationery', 'supply', 'equipment', 'material',
                        'tool', 'accessory', 'device', 'kit', 'set',
                    ];

                    $isUniversityRelated = $imageLabels->intersect($universityKeywords)->isNotEmpty();

                    Log::info('University-related check:', [
                        'is_university_related' => $isUniversityRelated,
                        'matching_keywords' => $imageLabels->intersect($universityKeywords)->toArray(),
                    ]);

                    if (! $isUniversityRelated) {
                        return response()->json([
                            'message' => 'This item does not appear to be university-related. Please ensure you are uploading academic or university-related items.',
                        ], 400);
                    }
                }

                // Save the compressed image
                $path = 'images/tmp/'.$folder.'/'.$uniqueFileName;
                Storage::disk('public')->put($path, $imageBytes);

                TemporaryImage::create([
                    'folder' => $folder,
                    'file' => $uniqueFileName,
                ]);

                return $folder;

            } catch (AwsException $e) {
                \Log::error('AWS Rekognition error: '.$e->getMessage(), [
                    'error_code' => $e->getAwsErrorCode(),
                    'error_type' => $e->getAwsErrorType(),
                    'request_id' => $e->getAwsRequestId(),
                ]);

                if ($e->getAwsErrorCode() === 'UnrecognizedClientException') {
                    return response()->json([
                        'message' => 'AWS authentication failed. Please contact support.',
                    ], 500);
                }

                return response()->json([
                    'message' => 'Error during content moderation. Please try again later.',
                ], 500);
            } catch (\Throwable $e) {
                \Log::error('Image processing error: '.$e->getMessage());

                return response()->json([
                    'message' => 'Error processing image. Please try again later.',
                ], 500);
            }
        }

        return response()->json(['message' => 'No image uploaded'], 400);
    }

    /**
     * Calculate optimal quality based on image dimensions and file size
     */
    private function calculateQuality(int $width, int $height, float $fileSize): int
    {
        // Base quality on resolution
        $megapixels = ($width * $height) / 1000000;
        $baseQuality = match (true) {
            $megapixels >= 8.3 => 65,  // 4K (3840x2160) and above
            $megapixels >= 3.7 => 70,  // 2K (2560x1440) and above
            $megapixels >= 2.1 => 75,  // 1080p (1920x1080) and above
            default => 80,
        };

        // Adjust quality based on file size
        $qualityReduction = match (true) {
            $fileSize > 8 => 25,
            $fileSize > 4 => 20,
            $fileSize > 2 => 15,
            $fileSize > 1 => 10,
            default => 0,
        };

        return max(40, $baseQuality - $qualityReduction);
    }

    /**
     * Smart resize based on image dimensions
     */
    private function smartResize($img, int $width, int $height)
    {
        $maxDimension = max($width, $height);

        return match (true) {
            $maxDimension > 3840 => $img->scale(width: 3840), // 4K
            $maxDimension > 2560 => $img->scale(width: 2560), // 2K
            $maxDimension > 1920 => $img->scale(width: 1920), // 1080p
            $maxDimension > 1200 => $img->scale(width: 1200), // Standard
            default => $img
        };
    }

    public function revert($file)
    {
        $tempImage = TemporaryImage::where('folder', $file)->first();
        // dd($tempImage);
        if ($tempImage) {
            Storage::disk('public')->deleteDirectory('images/tmp/'.$file);
            $tempImage->delete();
        }

        return '';
    }

    public function revertAll()
    {
        TemporaryImage::query()->delete();
        Storage::deleteDirectory('images/tmp/');

        return '';
    }
}
