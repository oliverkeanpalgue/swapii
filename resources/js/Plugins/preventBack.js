import { router } from '@inertiajs/vue3'

export default {
  install(app) {
    // Logging function for debugging
    const log = (message, data = {}) => {
      console.group('PreventBack Plugin')
      console.log(message, data)
      console.groupEnd()
    }

    // Prevent back navigation event
    const preventBackNavigation = (event) => {
      try {
        // Completely stop the back navigation
        event.preventDefault()
        event.stopPropagation()
        
        // Get authentication state from Inertia page props
        const page = app.config.globalProperties.$page
        const isAuthenticated = !!page?.props?.auth?.user
        const currentPath = window.location.pathname

        log('Prevent Back Triggered', {
          isAuthenticated,
          currentPath,
          referrer: document.referrer
        })

        // Determine if redirection is necessary
        let shouldRedirect = false
        let redirectPath = null

        if (isAuthenticated) {
          // If authenticated and on login, redirect to dashboard
          if (currentPath === '/login') {
            redirectPath = '/'
            shouldRedirect = true
          }
        } else {
          // If not authenticated, always redirect to login
          if (currentPath !== '/login') {
            redirectPath = '/login'
            shouldRedirect = true
          }
        }

        // Block any navigation attempt
        history.pushState(null, document.title, currentPath)
        
        // If redirection is needed, use a more controlled approach
        if (shouldRedirect) {
          log('Attempting Controlled Redirect', { redirectPath })
          
          // Use a timeout to ensure navigation is blocked
          setTimeout(() => {
            try {
              router.visit(redirectPath, { 
                replace: true,
                preserveState: false,
                onError: (errors) => {
                  log('Redirect Error', { errors })
                  
                  // Revert to current path if any error occurs
                  history.replaceState(null, document.title, currentPath)
                  alert('Navigation blocked. Please log in.')
                }
              })
            } catch (visitError) {
              log('Visit Method Error', { visitError })
              history.replaceState(null, document.title, currentPath)
            }
          }, 0)
        }
      } catch (error) {
        log('Error in preventBackNavigation', { error })
      }
    }

    // Add event listeners for popstate
    const handlePopState = (event) => {
      event.preventDefault()
      event.stopPropagation()
      preventBackNavigation(event)
      return false
    }

    // Override browser's default back behavior
    window.onpopstate = handlePopState

    // Expose debugging methods
    app.config.globalProperties.$preventBack = {
      log,
      preventBackNavigation
    }
  }
}
