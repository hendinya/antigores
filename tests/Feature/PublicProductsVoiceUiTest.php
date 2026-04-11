<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicProductsVoiceUiTest extends TestCase
{
    public function test_public_products_page_contains_voice_controls(): void
    {
        $response = $this->get('/offline-products');

        $response->assertOk();
        $response->assertSee('id="publicVoiceToggle"', false);
        $response->assertDontSee('id="publicVoiceLang"', false);
        $response->assertDontSee('id="publicVoiceStatus"', false);
    }

    public function test_public_products_page_contains_autofocus_logic(): void
    {
        $response = $this->get('/offline-products');

        $response->assertOk();
        $response->assertSee('keywordInput.focus', false);
    }
}
