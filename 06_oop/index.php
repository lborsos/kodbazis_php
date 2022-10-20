<?php

//new PDO()


class Termek  
{ 
    public function __construct(private $nev, private $ar)
    {
    }

    public function setNev($nev)
    {
        $this->nev = $nev;
    }

    public function adjKedvezmenyt()
    {
        $this->ar = $this->ar * 0.9;
    }

    public function getDescription() 
    {
        return "A termék neve: " . $this->nev . ", <br> ára: " . $this->ar . "<br>";
    }
} 

$termek1 = new Termek("iPhone 12", 349000);
$termek1->adjKedvezmenyt(); 
$termek1->setNev("iPhone 12 (akciós)");
echo $termek1->getDescription();

$termek2 = new Termek("Xiaomi Mi 11", 269000);
$termek2->adjKedvezmenyt();
$termek2->adjKedvezmenyt();
$termek2->adjKedvezmenyt();
$termek2->adjKedvezmenyt();
$termek2->adjKedvezmenyt();
echo $termek2->getDescription();