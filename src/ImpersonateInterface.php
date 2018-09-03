<?php
//StrictType
declare(strict_types = 1);

/*
 * Ness
 * Impersonate component
 *
 * Author CurtisBarogla <curtis_barogla@outlook.fr>
 *
 */
 
namespace Ness\Component\Ditto;

use Ness\Component\User\UserInterface;
use Ness\Component\Ditto\Exception\ImpersonateException;

/**
 * Allow to impersonate a specific user
 * 
 * @author CurtisBarogla <curtis_barogla@outlook.fr>
 *
 */
interface ImpersonateInterface
{
    
    /**
     * Impersonate the current user to the given one
     * 
     * @param UserInterface $user
     *   User to impersonate
     *   
     * @throws ImpersonateException
     *   When impersonation cannot be done
     */
    public function morph(UserInterface $user): void;
    
    /**
     * Restore the user before the impersonation
     * 
     * @throws ImpersonateException
     *   When old identity cannot be restored
     */
    public function demorph(): void;

    /**
     * Check if the current user is from a morph action
     * 
     * @return bool
     *   True if the user is under a morph session. False otherwise
     */
    public function isMorphed(): bool;
    
}
