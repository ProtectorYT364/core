<?php

namespace Core;

class PlayerConfig{

    /**
     * @var string
     */
    public string $deviceQueuing;

    /**
     * @var string
     */
    public string $javaInventory;

    /**
     * @var array|string[]
     */
    public array $configs = ['deviceQueuing', 'javaInventory'];


}