<?php

namespace Tests\Unit;

use Tests\TestCase;

class JobOfferLetterOfferTranslationTest extends TestCase
{
    public function test_offer_nested_key_is_string_for_datatable_column_keys(): void
    {
        $this->assertIsString(__('Offer.Offer'));
    }
}
