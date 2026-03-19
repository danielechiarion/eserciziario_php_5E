<?php
class Season{
    /* defining attributes */
    public int $year;
    public int $quantityTowels;
    public float $priceUmbrella;
    public float $priceTowels;
    public [] $umbrellas;

    /* create the structure */
    public function __construct(int $year, int $quantityTowels, float $priceUmbrella, float $priceTowels){
        $this->year = $year;
        $this->quantityTowels = $quantityTowels;
        $this->priceUmbrella = $priceUmbrella;
        $this->priceTowels = $priceTowels;
    }

    /* create function for the equals */
    public function equals(Season $season){
        return $season->year == $this->year;
    }

    /* function to add umbrellas */
    public function addUmbrella(int $umbrella){
        $this->umbrellas[] = $umbrella;
    }
}