<x-mail::message>
# Warning Notice

Dear User,

@if($warningLevel == 'level 1')
We are writing to inform you that your account has received a Level 1 warning due to a violation of our community guidelines. This is a formal notice to remind you of our platform's standards. While no immediate restrictions have been applied to your account, please take this as an opportunity to review our guidelines.
@elseif($warningLevel == 'level 2')
We are writing to inform you that your account has received a Level 2 warning due to a serious violation of our community guidelines. As a result, your account has been temporarily suspended for 3 days. This suspension will help ensure you have time to review and understand our community guidelines.
@elseif($warningLevel == 'level 3')
We are writing to inform you that your account has received a Level 3 warning due to a severe violation of our community guidelines. Due to the severity of this violation, your account has been suspended for 7 days. This extended suspension period reflects the seriousness of the violation.
@elseif($warningLevel == 'level 4')
We are writing to inform you that your account has received a Level 4 warning due to an extreme violation of our community guidelines. Due to the severity and nature of this violation, your account has been permanently banned from our platform. This decision is final and reflects our commitment to maintaining a safe and respectful community.
@endif

Please review our community guidelines to ensure your future activities align with our platform's standards. Repeated violations may result in further actions being taken on your account.

If you believe this warning was issued in error, please contact our support team.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
