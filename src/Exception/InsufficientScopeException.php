<?php namespace MagnesiumOxide\TwitchApi\Exception;

use MagnesiumOxide\TwitchApi\Scope;

/**
 * Exception that is thrown when the authenticated scope is insufficient.
 * @package MagnesiumOxide\TwitchApi\Exception
 */
class InsufficientScopeException extends \Exception implements TwitchException {
    /** @var String */
    protected $requiredScope = null;

    /**
     * A static factory to give a message and attach the scope to the exception.
     *
     * @param String $requiredScope
     * @return InsufficientScopeException
     */
    public static function createException($requiredScope, $availableScopes = []) {
        $message = $requiredScope . " is required.";
        if (!empty($availableScopes)) {
            $message = $message . " " . implode(" ", $availableScopes) . " are available.";
        }
        $exception = new InsufficientScopeException($message);
        $exception->setRequiredScope($requiredScope);
        return $exception;
    }

    /** @return null|string */
    public function getRequiredScope() {
        return $this->requiredScope;
    }

    /** @param String $scope */
    protected function setRequiredScope($scope) {
        $this->requiredScope = $scope;
    }
}