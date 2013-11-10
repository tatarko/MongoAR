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
 * @package MongoAR
 * @author Tomas Tatarko <tomas@tatarko.sk>
 * @copyright (c) 2013, Tomas Tatarko
 * @license http://choosealicense.com/licenses/mit/ The MIT License
 * @link https://github.com/tatarko/MongoAR Official github repo
 * @since 0.1
 */
class Exception extends BasicException
{

}