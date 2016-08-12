<?php

namespace Rz\Service;

use Riimu\Kit\SecureRandom\Generator\AbstractGenerator;
use Riimu\Kit\SecureRandom\GeneratorException;

class ControlledGenerator extends AbstractGenerator
{
  /**
   * Reads bytes from the randomness source.
   * @param int $count number of bytes to read
   * @return string|false The bytes read from the randomness source or false on error
   * @throws GeneratorException If error occurs in byte generation
   */
  protected function readBytes($count)
  {
    // Length: 64
    $string = '1234567890123456789012345678901234567890123456789012345678901234';

    return substr($string, 0, $count);
  }

  /**
   * Tells if the generator is supported by the operating system.
   * @return bool True if the generator is supported, false if not
   */
  public function isSupported()
  {
    return true;
  }
}