UPDATE `#__gafinance_invoices` SET `inv_date` = NULL WHERE `inv_date` = "0000-00-00";
UPDATE `#__gafinance_invoices` SET `paid_date` = NULL WHERE `paid_date` = "0000-00-00";
UPDATE `#__gafinance_budgets` SET `item_date` = NULL WHERE `item_date` = "0000-00-00";
UPDATE `#__gafinance_busassets` SET `asset_date` = NULL WHERE `asset_date` = "0000-00-00";
