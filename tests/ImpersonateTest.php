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
 
namespace NessTest\Component\Ditto;

use Ness\Component\Ditto\Impersonate;
use Ness\Component\User\Storage\UserStorageInterface;
use Ness\Component\Ditto\Store\ImpersonateStoreInterface;
use Ness\Component\User\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Ness\Component\Ditto\Exception\ImpersonateException;

/**
 * Impersonate testcase
 * 
 * @see \Ness\Component\Ditto\Impersonate
 * 
 * @author CurtisBarogla <curtis_barogla@outlook.fr>
 *
 */
class ImpersonateTest extends ImpersonateTestCase
{
    
    /**
     * @see \Ness\Component\Ditto\Impersonate::morph()
     */
    public function testMorph(): void
    {
        $userMorphed = $this->getMockBuilder(UserInterface::class)->getMock();
        $userTarget = $this->getMockBuilder(UserInterface::class)->getMock();
        
        $action = function(MockObject $userStore, MockObject $impersonateStore) use ($userMorphed, $userTarget): void {
            $userStore->expects($this->once())->method("get")->will($this->returnValue($userMorphed));
            $userStore->expects($this->once())->method("refresh")->with($userTarget)->will($this->returnValue(true));
            $impersonateStore->expects($this->once())->method("store")->with($userMorphed)->will($this->returnValue(true));
        };
        
        $impersonate = $this->getImpersonate($action);
        
        $this->assertNull($impersonate->morph($userMorphed));
    }
    
    /**
     * @see \Ness\Component\Ditto\Impersonate::demorph()
     */
    public function testDemorph(): void
    {
        $userMorphed = $this->getMockBuilder(UserInterface::class)->getMock();
        $userTarget = $this->getMockBuilder(UserInterface::class)->getMock();
        
        $action = function(MockObject $userStore, MockObject $impersonateStore) use ($userMorphed, $userTarget): void {
            $impersonateStore->expects($this->once())->method("get")->will($this->returnValue($userMorphed));
            $userStore->expects($this->once())->method("refresh")->with($userMorphed)->will($this->returnValue(true));
            $impersonateStore->expects($this->once())->method("delete");
        };
        
        $impersonate = $this->getImpersonate($action);
        
        $this->assertNull($impersonate->demorph($userMorphed));
    }
    
    /**
     * @see \Ness\Component\Ditto\Impersonate::isMorphed()
     */
    public function testIsMorphed(): void
    {
        $userMorphed = $this->getMockBuilder(UserInterface::class)->getMock();
        $userTarget = $this->getMockBuilder(UserInterface::class)->getMock();
        
        $action = function(MockObject $userStore, MockObject $impersonateStore) use ($userMorphed, $userTarget): void {
            $impersonateStore->expects($this->exactly(2))->method("get")->will($this->onConsecutiveCalls($userMorphed, null));
        };
        
        $impersonate = $this->getImpersonate($action);
        
        $this->assertTrue($impersonate->isMorphed());
        $this->assertFalse($impersonate->isMorphed());
    }
    
                    /**_____EXCEPTIONS_____**/
    
    /**
     * @see \Ness\Component\Ditto\Impersonate::morph()
     */
    public function testExceptionMorphWhenNoUserFoundIntoUserStore(): void
    {
        $this->expectException(ImpersonateException::class);
        $this->expectExceptionMessage("Cannot morph to a user as no base user has been found into user store");
        $this->expectExceptionCode(Impersonate::USER_NOT_FOUND);
        
        $action = function(MockObject $userStore, MockObject $impersonateStore): void {
            $userStore->expects($this->once())->method("get")->will($this->returnValue(null));
        };
        
        $impersonate = $this->getImpersonate($action);
        $impersonate->morph($this->getMockBuilder(UserInterface::class)->getMock());
    }
    
    /**
     * @see \Ness\Component\Ditto\Impersonate::morph()
     */
    public function testExceptionMorphWhenUserStoreRefreshFailed(): void
    {
        $userMorphed = $this->getMockBuilder(UserInterface::class)->getMock();
        $userTarget = $this->getMockBuilder(UserInterface::class)->getMock();
        $this->expectExceptionCode(Impersonate::STORE_ERROR);
        
        $this->expectException(ImpersonateException::class);
        $this->expectExceptionMessage("An error happen during the morphing process. All user session data has been removed");
        
        $action = function(MockObject $userStore, MockObject $impersonateStore) use ($userMorphed, $userTarget): void {
            $userStore->expects($this->once())->method("get")->will($this->returnValue($userMorphed));
            $userStore->expects($this->once())->method("refresh")->with($userTarget)->will($this->returnValue(false));
            $userStore->expects($this->once())->method("delete");
            $impersonateStore->expects($this->once())->method("delete");
        };
        
        $impersonate = $this->getImpersonate($action);
        $impersonate->morph($userTarget);
    }
    
    /**
     * @see \Ness\Component\Ditto\Impersonate::morph()
     */
    public function testExceptionMorphWhenImpersonateStoreFailed(): void
    {
        $userMorphed = $this->getMockBuilder(UserInterface::class)->getMock();
        $userTarget = $this->getMockBuilder(UserInterface::class)->getMock();
        $this->expectExceptionCode(Impersonate::STORE_ERROR);
        
        $this->expectException(ImpersonateException::class);
        $this->expectExceptionMessage("An error happen during the morphing process. All user session data has been removed");
        
        $action = function(MockObject $userStore, MockObject $impersonateStore) use ($userMorphed, $userTarget): void {
            $userStore->expects($this->once())->method("get")->will($this->returnValue($userMorphed));
            $userStore->expects($this->once())->method("refresh")->with($userTarget)->will($this->returnValue(true));
            $userStore->expects($this->once())->method("delete");
            $impersonateStore->expects($this->once())->method("store")->with($userMorphed)->will($this->returnValue(false));
            $impersonateStore->expects($this->once())->method("delete");
        };
        
        $impersonate = $this->getImpersonate($action);
        $impersonate->morph($userTarget);
    }
    
    /**
     * @see \Ness\Component\Ditto\Impersonate::demorph()
     */
    public function testExceptionDemorphWhenOriginalUserCannotBeGetted(): void
    {
        $this->expectException(ImpersonateException::class);
        $this->expectExceptionMessage("Cannot get the original user from the store. Did you forget to morph ?");
        $this->expectExceptionCode(Impersonate::USER_NOT_FOUND);
        
        $action = function(MockObject $userStore, MockObject $impersonateStore): void {
            $impersonateStore->expects($this->once())->method("get")->will($this->returnValue(null));
        };
        
        $impersonate = $this->getImpersonate($action);
        $impersonate->demorph();
    }
    
    /**
     * @see \Ness\Component\Ditto\Impersonate::demorph()
     */
    public function testExceptionDemorphWhenUserStoreCannotBeRefreshedWithTheOriginalUser(): void
    {
        $userMorphed = $this->getMockBuilder(UserInterface::class)->getMock();
        $userTarget = $this->getMockBuilder(UserInterface::class)->getMock();
        $this->expectExceptionCode(Impersonate::STORE_ERROR);
        
        $this->expectException(ImpersonateException::class);
        $this->expectExceptionMessage("An error happen during the demorphing process. All user session data has been removed");
        
        $action = function(MockObject $userStore, MockObject $impersonateStore) use ($userMorphed, $userTarget): void {
            $impersonateStore->expects($this->once())->method("get")->will($this->returnValue($userMorphed));
            $userStore->expects($this->once())->method("refresh")->with($userTarget)->will($this->returnValue(false));
            $userStore->expects($this->once())->method("delete");
            $impersonateStore->expects($this->once())->method("delete");
        };
        
        $impersonate = $this->getImpersonate($action);
        $impersonate->demorph($userTarget);
    }
    
    /**
     * Get an Impersonate instane with user stor and impersonate store setted
     * 
     * @param \Closure|null $action
     *   Action done on the user store and the impersonate store
     * 
     * @return Impersonate
     *   Impersonate instance
     */
    private function getImpersonate(?\Closure $action = null): Impersonate
    {
        $userStore = $this->getMockBuilder(UserStorageInterface::class)->getMock();
        $impersonateStore = $this->getMockBuilder(ImpersonateStoreInterface::class)->getMock();
        
        if(null !== $action)
            $action->call($this, $userStore, $impersonateStore);
        
        return new Impersonate($userStore, $impersonateStore);
    }
    
}
