# Impersonate Component

This library allows you to impersonate a user of your application

0. [How to install](#0-installing-the-component)
1. [Why ?](#1-why)
2. [How to use](#2-how-to-use)
3. [Impersonate Store](#3-impersonate-store)
4. [Impersonation](#4-impersonation)
5. [Contributing](#5-contributing)
6. [License](#6-license)

## 0. Installing the component

Ditto library can be installed via composer

~~~bash
$ composer require ness/ditto
~~~

**Requires** : 
- [ness/user](https://github.com/CurtisBarogla/User) library

~~~bash
$ composer require ness/user
~~~

## 1. Why ?

Sometimes, it can be useful for debugging or assist reasons to be able to act as a specific user.

This library allows you, via the user library, to impersonate a specific user of your application in the same time persisting your old "user's identity". 

This library is **fully unit tested**.

## 2. How to use

This library consists in a simple interface allowing you to morph, demorph a specific user.

Let's see a simple use case with the implementation proposed.

This implementation requires a [UserStorageInterface](https://github.com/CurtisBarogla/User#2-storing-user) implementation from the user library and an ImpersonateStoreInterface implementation.

**! Important !**

For this example to work, session MUST be enabled or a LogicException will be thrown.

~~~php
$userStore = new UserStorageImplementation();
$impersonateStore = new NativeSessionImpersonateStore();
$impersonate = new Impersonate($userStore, $impersonateStore);

// let's admit this user is currently the accessible one for the current user's session
$currentUser = new User("AdminUser", null, ["ROLE_ADMIN"]);

// the user we want to impersonate
$toImpersonateUser = new User("FooUser", ["foo" => "bar"], ["ROLE_MEMBER"]);

$userStore->get(); // will return $currentUser
// checking if we are currently in an impersonating a user
$impersonate->isMorphed(); // will return false in this situation

// now let's morph
$impersonate->morph($toImpersonateUser); // $currentUser is now impersonating $toImpersonateUser
$userStore->get(); // will return $impersonateUser
$impersonate->isMorphed(); // will return true

// now let's demorph
$impersonate->demorph(); // we are returning in our original state
$userStore->get(); // will return $currentUser
$impersonate->isMorphed(); // will return false
~~~

## 3. Impersonate Store

The impersonate store is a simple interface providing you a to store a user who has impersonate another user. 

It consists in 3 methods :

- **get()** which will return the original user. Will return null if no user has been impersonated,
- **store()** which will add the original state of a user, returns true if the user has been stored with success
- **delete()** which will simply clear the original user from the store, returns true if the user has been removed from the store.

**! Important !**

Delete method MUST only return false if a user is actually stored for the current session and cannot be removed for whatever reason. Never when the store is empty for the current session.

### 3.1 NativeSessionImpersonateStore

This library comes with a basic implementation of ImpersonateStoreInterface.

This implementation will basically use the native session mechanism of PHP to store, get and remove an original user.

**! Important !**

Session MUST be active when this storage is constructed or a LogicException will be thrown.
 
~~~php
$store = new NativeSessionImpersonateStore();
$user = new User("Foo");

$store->get(); // will return null. no user has been setted

$store->store($user); // returns true

$store->get(); // returns $user

$store->delete(); // remove the user from the store
~~~
 
## 4. Impersonation

ImpersonationInterface allows you to impersonate a user of your application for whatever reasons and restore your old identity after your operation is done on the impersonate user.

It consists in some basic methods :

Method **morph()** which allows you to morph your current user's identity to another one.
Method **demorph()** which will restore your old user's identity from a precedent call to morph().
Method **isMorphed** which will simply check if your current identity is from a morph call.

### 4.1 Implementation

This library comes with an implementation of ImpersonationInterface.

This implementation is based on a UserStore provided by the [user](https://github.com/CurtisBarogla/User) library and a ImpersonateStore.

Morphing operations requires a **writable** ImpersonateStore and a **writable** UserStore.

Trying to morph into a user with no basic user setted into the UserStore will result a ImpersonateException.

If an error happen during the morphing process, basicaly when the user store fails to refresh the current user with the morphed user or when the impersonate store fails to save the current identity, all current user's informations will be lost and a ImpersonateException will be thrown.

Trying to demorph if not currently morphed will result a ImpersonateException ; to avoid this we can check if we are currently morphed via **isMorphed()** method.

If the user store fails to refresh the current user with the one stored into the impersonate store, a ImpersonateException will be raised too and all user's informations will be lost.

~~~php
$impersonateStore = new ImpersonateStoreImplementation();
$userStore = new UserStoreImplementation();
$impersonate = new Impersonate($userStore, $impersonateStore);

// let's admit $userStore is providing a FooUser
$userStore->get(); // will return a FooUser
$impersonate->morph(new User("BarUser"));
$userStore->get(); // will return a BarUser

// checking if we are currently morphed
$impersonate->isMorphed(); // will return true

// now that we are morphed into BarUser, we can retrieve our "true" identity via demorph
$impersonate->demorph();
$userStore->get(); // will return FooUser

$impersonate->isMorphed(); // will return false

// let's admit no user has been previously stored
$userStore->get(); // returns null
$impersonate->morph(new User("BarUser")); // will throw a ImpersonateException

// let's admit we've not called morph method
$impersonate->demorph(); // will throw a ImpersonateException
~~~

## 5. Contributing

Found something **wrong** (nothing is perfect) ? Wanna talk or participate ? <br />
Issue the case or contact me at [curtis_barogla@outlook.fr](mailto:curtis_barogla@outlook.fr)

## 6. License

The Ness Ditto component is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
