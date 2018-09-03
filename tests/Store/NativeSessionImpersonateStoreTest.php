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

namespace Ness\Component\Ditto\Store {
    
    $started = null;
    
    /**
     * Initialize globals
     *
     * @var int $sessionStatus
     *   Current session status. Const defined into php
     */
    function initGlobals(int $sessionStatus): void
    {
        global $started;
        
        $started = $sessionStatus;
    }
    
    /**
     * Mock session_status
     *
     * @return int
     *   Session status defined by call to initGlobals
     */
    function session_status()
    {
        global $started;
        
        return $started;
    }
    
}

namespace NessTest\Component\Ditto\Store {

    use NessTest\Component\Ditto\ImpersonateTestCase;
    use Ness\Component\Ditto\Store\NativeSessionImpersonateStore;
    use Ness\Component\User\UserInterface;
    use function Ness\Component\Ditto\Store\initGlobals;
                                                                    
    /**
     * Simply use the native session mechanism of php to store the user 
     * 
     * @author CurtisBarogla <curtis_barogla@outlook.fr>
     *
     */
    class NativeSessionImpersonateStoreTest extends ImpersonateTestCase
    {
    
        /**
         * @see \Ness\Component\Ditto\Store\NativeSessionImpersonateStore::get()
         */
        public function testGet(): void
        {
            $user = $this->getMockBuilder(UserInterface::class)->getMock();
            
            initGlobals(PHP_SESSION_ACTIVE);
            $store = new NativeSessionImpersonateStore();
            $this->injectSession($store);
            $this->assertNull($store->get());
            $store->store($user);
            $this->assertSame($user, $store->get());
        }
        
        /**
         * @see \Ness\Component\Ditto\Store\NativeSessionImpersonateStore::store()
         */
        public function testStore(): void
        {
            $user = $this->getMockBuilder(UserInterface::class)->getMock();
            
            initGlobals(PHP_SESSION_ACTIVE);
            $store = new NativeSessionImpersonateStore();
            $this->injectSession($store);
            
            $this->assertTrue($store->store($user));
        }
        
        /**
         * @see \Ness\Component\Ditto\Store\NativeSessionImpersonateStore::delete()
         */
        public function testDelete(): void
        {
            $user = $this->getMockBuilder(UserInterface::class)->getMock();
            
            initGlobals(PHP_SESSION_ACTIVE);
            $store = new NativeSessionImpersonateStore();
            $this->injectSession($store);
            $store->store($user);
            $this->assertNull($store->delete());
        }
        
                        /**_____EXCEPTIONS____**/
        
        /**
         * @see \Ness\Component\Ditto\Store\NativeSessionImpersonateStore::__construct()
         */
        public function testExceptionWhenSessionNotInitialized(): void
        {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage("Session MUST be active for using the NativeSessionImpersonateStore component");
            
            initGlobals(PHP_SESSION_NONE);
            $store = new NativeSessionImpersonateStore();
        }
        
        /**
         * Inject an array as session property
         * 
         * @param NativeSessionImpersonateStore $store
         *   Store which to inject the array
         */
        private function injectSession(NativeSessionImpersonateStore $store): void
        {
            $reflection = new \ReflectionClass($store);
            $property = $reflection->getProperty("session");
            $property->setAccessible(true);
            $property->setValue($store, []);
        }
        
    }

}
