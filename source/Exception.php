<?php

namespace MongoAR;

use Exception as BasicException;

/**
 * Custom Expcetion
 * 
 * Whole MongoDB Active Record throws
 * this exception in case of some error.
 * So you can easily track errors coming
 * from ActiveRecord itself.
 * 
 * @since 0.1
 * @author Tomas Tatarko <tomas@tatarko.sk>
 * @license http://choosealicense.com/licenses/mit/ The MIT License
 * @link https://github.com/tatarko/MongoAR Official github repo
 */
class Exception extends BasicException
{
    
}