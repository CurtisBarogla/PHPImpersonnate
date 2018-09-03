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
 
namespace Ness\Component\Ditto\Store;

use Ness\Component\User\UserInterface;

/**
 * Interacts with an external storage component to handle the impersonating process
 * 
 * @author CurtisBarogla <curtis_barogla@outlook.fr>
 *
 */
interface ImpersonateStoreInterface
{
    
    /**
     * Get a user before the impersonating process happen
     * 
     * @return UserInterface|null
     *   Original user or null if no user stored
     */
    public function get(): ?UserInterface;
    
    /**
     * Store an original user to persist it during the impersonating process
     * 
     * @param UserInterface $user
     *   Original user state
     * 
     * @return bool
     *   True if the user has been store with success. False otherwise
     */
    public function store(UserInterface $user): bool;
    
    /**
     * Delete the original user from the store
     */
    public function delete(): void;
    
}
