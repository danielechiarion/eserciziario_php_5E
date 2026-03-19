<?php
class Purchase{
    /* define attributes */
    public int $ID;
    public Person $client;
    public int $quantityTowels;
    public DateTime $startingDate;
    public DateTime $endingDate;
    public float $price;
    public [] $umbrellas;
    public [] $beachLoungers;

    /* constructor of the class */
    public function __construct(int $ID, Person $client, int $quantityTowels, DateTime $startingDate, Datetime $endingDate, float $price){
        $this->ID = $ID;
        $this->client = $client;
        $this->quantityTowels = $quantityTowels;
        $this->startingDate = $startingDate;
        $this->endingDate = $endingDate;
        $this->price = $price;
    }

    /* equals function */
    public function equals(Purchase $other){
        return $this->ID === $other->ID;
    }

    /* function to add an umbrella */
    public function addUmbrella(int $umbrella){
        $this->umbrellas[] = $umbrella;
    }

    /* function to add beach loungers */
    public function addBeachLounger(int $beachLounger){
        $this->beachLoungers[] = $beachLounger;
    }

    /* function to calculate price */
    public function calculatePrice(Season $season){
        return $this->quantityTowels*$season->priceTowels + count($this->umbrellas) * $season->priceUmbrella;
    }
}