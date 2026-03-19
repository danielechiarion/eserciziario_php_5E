<?php
enum AccountType:string{
    case Administrator = 'admin';
    case Client = 'client';
}
class Account{
    /* define attributes */
    public string $username;
    public ?Person $client;
    public AccountType $role;

    /* constructor of the function */
    public function __construct(string $username, AccountType $role, Person $client = null){
        $this->username = $username;
        $this->client = $client;
        $this->role = $role;
    }

    /* function similar to equals */
    public function equals(Account $account){
        return $account->username == $this->username;
    }

    /* function to understand if the user has
    priviledged access */
    public function isAdmin(){
        return $this->role == AccountType::Administrator;
    }
}