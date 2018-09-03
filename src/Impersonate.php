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
use Ness\Component\User\Storage\UserStorageInterface;
use Ness\Component\Ditto\Store\ImpersonateStoreInterface;
use Ness\Component\Ditto\Exception\ImpersonateException;

/**
 * Native implementation of ImpersonateInterface.
 * Use a UserStore and a Impersonate store
 * 
 * @author CurtisBarogla <curtis_barogla@outlook.fr>
 *
 */
class Impersonate implements ImpersonateInterface
{
    
    /**
     * User store
     * 
     * @var UserStorageInterface
     */
    private $userStore;
    
    /**
     * Impersonate store
     * 
     * @var ImpersonateStoreInterface
     */
    private $impersonateStore;
    
    /**
     * When the user from the user store or the impersonate store not found
     * 
     * @var int
     */
    public const USER_NOT_FOUND = 0;
    
    /**
     * When a store cannot failed to set a user
     * 
     * @var int
     */
    public const STORE_ERROR = 1;
    
    /**
     * Initialize impersonate mechanism
     * 
     * @param UserStorageInterface $userStore
     *   User store
     * @param ImpersonateStoreInterface $impersonateStore
     *   Impersonate store
     */
    public function __construct(UserStorageInterface $userStore, ImpersonateStoreInterface $impersonateStore)
    {
        $this->userStore = $userStore;
        $this->impersonateStore = $impersonateStore;
    }
    
    /**
     * {@inheritDoc}
     * @see \Ness\Component\Ditto\ImpersonateInterface::morph()
     */
    public function morph(UserInterface $user): void
    {
        if(null === $original = $this->userStore->get())
            throw new ImpersonateException("Cannot morph to a user as no base user has been found into user store", self::USER_NOT_FOUND);
        
        if(!$this->userStore->refresh($user) || !$this->impersonateStore->store($original)){
            $this->userStore->delete();
            $this->impersonateStore->delete();
            
            throw new ImpersonateException("An error happen during the morphing process. All user session data has been removed", self::STORE_ERROR);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Ness\Component\Ditto\ImpersonateInterface::demorph()
     */
    public function demorph(): void
    {
        if(null === $original = $this->impersonateStore->get())
            throw new ImpersonateException("Cannot get the original user from the store. Did you forget to morph ?", self::USER_NOT_FOUND);
        
        if(!$this->userStore->refresh($original)) {
            $this->userStore->delete();
            $this->impersonateStore->delete();
            
            throw new ImpersonateException("An error happen during the demorphing process. All user session data has been removed", self::STORE_ERROR);   
        }
        
        $this->impersonateStore->delete();
    }

    /**
     * {@inheritDoc}
     * @see \Ness\Component\Ditto\ImpersonateInterface::isMorphed()
     */
    public function isMorphed(): bool
    {
        return null !== $this->impersonateStore->get();
    }

}
