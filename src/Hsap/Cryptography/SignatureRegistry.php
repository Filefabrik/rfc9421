<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Cryptography;

final class SignatureRegistry
{

    /**
     * Named algos
     * @var array 
     */
    private static array $algos = [];

    /**
     * Methods with they will be called by the $algos 
     * @var array 
     *           
     */
    private static array $methods = [];
    
    
    public static function signing(string$signingDatas,string$privateKey)
    {
        
    }
    
    
}
