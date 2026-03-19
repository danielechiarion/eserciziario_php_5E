<?php
class Person{
    /* defining attributes */
    public string $CF;
    public string $name;
    public string $surname;

    /* defining constructor method */
    public function __construct(string $CF, string $name, string $surname){
        $this->CF = $CF;
        $this->name = $name;
        $this->surname = $surname;
    }

    /* equals method for the person object */
    public function equals(Person $person){
        return $this->CF == $person->CF;
    }
}