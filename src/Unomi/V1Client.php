<?php

namespace Bolius\UnomiClient\Unomi;

class V1Client extends Client
{

    /**
     * @return mixed
     */
    public function getSegments()
    {
        return $this->get('/segments');
    }

}