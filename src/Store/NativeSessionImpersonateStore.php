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
 * Simply use the native session mechanism of php to store the user 
 * 
 * @author CurtisBarogla <curtis_barogla@outlook.fr>
 *
 */
class NativeSessionImpersonateStore implements ImpersonateStoreInterface
{
    
    /**
     * Session
     * 
     * @var array
     */
    private $session;
    
    /**
     * Impersonate identifier
     * 
     * @var string
     */
    public const IMPERSONATE_IDENTIFIER = "NESS_IMPERSONATE";
    
    /**
     * Initialize impersonate store
     * 
     * @throws \LogicException
     *   When session not active
     */
    public function __construct()
    {
        if(session_status() !== PHP_SESSION_ACTIVE)
            throw new \LogicException("Session MUST be active for using the NativeSessionImpersonateStore component");
        
        $this->session =& $_SESSION;
    }
    
    /**
     * {@inheritDoc}
     * @see \Ness\Component\Ditto\Store\ImpersonateStoreInterface::get()
     */
    public function get(): ?UserInterface
    {
        return $this->session[self::IMPERSONATE_IDENTIFIER] ?? null;
    }

    /**
     * {@inheritDoc}
     * @see \Ness\Component\Ditto\Store\ImpersonateStoreInterface::store()
     */
    public function store(UserInterface $user): bool
    {
        $this->session[self::IMPERSONATE_IDENTIFIER] = $user;
        
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Ness\Component\Ditto\Store\ImpersonateStoreInterface::delete()
     */
    public function delete(): void
    {
        unset($this->session[self::IMPERSONATE_IDENTIFIER]);
    }
    
}
