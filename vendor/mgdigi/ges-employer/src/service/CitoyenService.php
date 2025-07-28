<?php 

namespace App\Service;

use App\Core\App;
use App\Core\Singleton;
use App\Repository\CitoyenRepository;

class CitoyenService extends Singleton{

    private CitoyenRepository $citoyenRepository;

    public function __construct(CitoyenRepository $citoyenRepository){
        $this->citoyenRepository = $citoyenRepository;

    }


    public function getAllCitoyens(){
        return $this->citoyenRepository->selectAll();
    }

    public function  getCitoyenByCni($cni){
        return $this->citoyenRepository->selectByCni($cni);
    }


}