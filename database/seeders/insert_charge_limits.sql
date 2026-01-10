-- Insert charge_limits data if not exists
-- This file contains the missing charge_limits data

INSERT INTO `charge_limits` (`id`, `slug`, `min_amount`, `max_amount`, `fixed_charge`, `percent_charge`, `vat`, `pickup_fee`, `created_at`, `updated_at`) 
VALUES
(1, 'buy', 10.00000000, 10000.00000000, 1.00000000, 1.00, 2.50, 0.00, NULL, '2025-12-15 09:10:42'),
(2, 'sell', 10.00000000, 10000.00000000, 2.00000000, 3.00, 0.00, 0.00, NULL, '2024-12-11 06:04:43'),
(3, 'gift', 10.00000000, 10000.00000000, 1.00000000, 2.00, 0.00, 0.00, NULL, '2024-12-11 06:04:47'),
(4, 'redeem', 10.00000000, 10000.00000000, 0.00000000, 0.00, 0.00, 15.00, NULL, '2025-12-25 13:03:30')
ON DUPLICATE KEY UPDATE 
    `min_amount` = VALUES(`min_amount`),
    `max_amount` = VALUES(`max_amount`),
    `fixed_charge` = VALUES(`fixed_charge`),
    `percent_charge` = VALUES(`percent_charge`),
    `vat` = VALUES(`vat`),
    `pickup_fee` = VALUES(`pickup_fee`),
    `updated_at` = VALUES(`updated_at`);

