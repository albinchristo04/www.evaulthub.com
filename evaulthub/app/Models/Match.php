<?php

namespace App\Models;

/*
 * PHP reserves the `match` keyword, so the concrete Eloquent model lives in MatchModel.
 * This alias keeps the expected App\Models\Match symbol available where needed.
 */
class_alias(MatchModel::class, __NAMESPACE__.'\\Match');
