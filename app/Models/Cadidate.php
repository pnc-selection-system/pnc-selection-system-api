<?php

namespace App\Models;

/**
 * Alias for the Candidate model.
 *
 * Some parts of the codebase reference 'Cadidate' (missing the 'n').
 * This class bridges that typo so the autoloader can find the file.
 */
class Cadidate extends Candidate
{
    // All functionality is inherited from Candidate
}
