<?php

/**
 * Bootstrap class for initializing the application
 * Handles routing and application startup
 */
class bootstrap {
    
    public function __construct() {
        $this->setSecurityHeaders();
        $this->initializeRoutes();
        $this->startRouter();
    }
    
    /**
     * Set security headers
     */
    private function setSecurityHeaders() {
        SecurityHeaders::setHeaders();
    }
    
    /**
     * Initialize routes from RouteFactory
     */
    private function initializeRoutes() {
        try {
            new RouteFactory();
        } catch (Exception $e) {
            // Handle route initialization errors gracefully
            error_log("Route initialization failed: " . $e->getMessage());
        }
    }
    
    /**
     * Start the router to handle incoming requests
     */
    private function startRouter() {
        try {
            new router();
        } catch (Exception $e) {
            // Handle routing errors gracefully
            error_log("Router initialization failed: " . $e->getMessage());
            $this->handleError();
        }
    }
    
    /**
     * Handle bootstrap errors
     */
    private function handleError() {
        // Redirect to error page or show generic error
        if (class_exists('error_handler')) {
            $error = new error_handler();
            if (method_exists($error, 'missing_page')) {
                $error->missing_page();
            }
        }
    }
}