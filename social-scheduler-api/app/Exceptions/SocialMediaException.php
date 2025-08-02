<?php

namespace App\Exceptions;

use Exception;

class SocialMediaException extends Exception
{
    protected $platform;
    protected $postId;
    protected $retryable;
    
    public function __construct($message, $platform = null, $postId = null, $retryable = true)
    {
        parent::__construct($message);
        $this->platform = $platform;
        $this->postId = $postId;
        $this->retryable = $retryable;
    }
    
    public function getPlatform()
    {
        return $this->platform;
    }
    
    public function getPostId()
    {
        return $this->postId;
    }
    
    public function isRetryable()
    {
        return $this->retryable;
    }
    
    /**
     * Get the exception's context information.
     */
    public function context(): array
    {
        return [
            'platform' => $this->platform,
            'post_id' => $this->postId,
            'retryable' => $this->retryable,
            'message' => $this->getMessage(),
        ];
    }
} 