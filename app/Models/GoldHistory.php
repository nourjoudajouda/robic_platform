<?php

namespace App\Models;

/**
 * Backward-compatible alias for older admin code.
 *
 * The project renamed `gold_histories` to `bean_history` and introduced
 * `App\Models\BeanHistory`. Some admin routes/controllers still reference
 * `GoldHistory`, so this class keeps them working.
 */
class GoldHistory extends BeanHistory
{
    // Intentionally empty.
}

