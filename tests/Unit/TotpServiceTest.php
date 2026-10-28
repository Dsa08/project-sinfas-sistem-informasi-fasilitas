<?php

namespace Tests\Unit;

use App\Services\TotpService;
use PHPUnit\Framework\TestCase;

/**
 * Pengujian Unit: TotpService (RFC 6238 Standard)
 */
class TotpServiceTest extends TestCase
{
    public function test_generate_secret_returns_valid_base32_string(): void
    {
        $secret = TotpService::generateSecret(16);

        $this->assertSame(16, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function test_calculate_otp_returns_six_digit_string(): void
    {
        $secret = TotpService::generateSecret();
        $otp = TotpService::calculateOtp($secret);

        $this->assertSame(6, strlen($otp));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);
    }

    public function test_verify_returns_true_for_valid_current_otp(): void
    {
        $secret = TotpService::generateSecret();
        $otp = TotpService::calculateOtp($secret);

        $this->assertTrue(TotpService::verify($secret, $otp));
    }

    public function test_verify_returns_false_for_invalid_otp(): void
    {
        $secret = TotpService::generateSecret();

        $this->assertFalse(TotpService::verify($secret, '000000'));
        $this->assertFalse(TotpService::verify($secret, 'abcdef'));
        $this->assertFalse(TotpService::verify($secret, '123'));
    }

    public function test_generate_recovery_codes_returns_eight_distinct_codes(): void
    {
        $codes = TotpService::generateRecoveryCodes(8);

        $this->assertCount(8, $codes);
        $this->assertCount(8, array_unique($codes));

        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/', $code);
        }
    }
}
