<?php

namespace Tests\Unit;

use App\Services\MediaStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaStorageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['media.disk' => 'public', 'media.mirror_disk' => null]);

        Storage::fake('public');
        Storage::fake('r2');
    }

    public function test_it_stores_the_file_on_the_primary_disk_and_returns_a_relative_path(): void
    {
        $path = MediaStorage::store(UploadedFile::fake()->image('selfie.jpg'), 'attendances', 'check_in.jpg');

        $this->assertSame('attendances/check_in.jpg', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_it_does_not_write_to_the_mirror_when_mirroring_is_off(): void
    {
        $path = MediaStorage::store(UploadedFile::fake()->image('selfie.jpg'), 'attendances', 'check_in.jpg');

        Storage::disk('r2')->assertMissing($path);
    }

    public function test_it_writes_to_both_disks_when_mirroring_is_on(): void
    {
        config(['media.mirror_disk' => 'r2']);

        $path = MediaStorage::store(UploadedFile::fake()->image('selfie.jpg'), 'patrols', 'patrol.jpg');

        Storage::disk('public')->assertExists($path);
        Storage::disk('r2')->assertExists($path);
        $this->assertSame(
            Storage::disk('public')->get($path),
            Storage::disk('r2')->get($path),
            'The mirrored copy should be byte-identical to the primary copy.'
        );
    }

    public function test_a_broken_mirror_disk_does_not_fail_the_upload(): void
    {
        Log::spy();
        config(['media.mirror_disk' => 'this-disk-does-not-exist']);

        $path = MediaStorage::store(UploadedFile::fake()->image('selfie.jpg'), 'checkpoints', 'cp.jpg');

        Storage::disk('public')->assertExists($path);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_url_resolves_through_the_configured_media_disk(): void
    {
        $this->assertSame(
            Storage::disk('public')->url('attendances/a.jpg'),
            MediaStorage::url('attendances/a.jpg')
        );

        config(['media.disk' => 'r2']);

        $this->assertSame(
            Storage::disk('r2')->url('attendances/a.jpg'),
            MediaStorage::url('attendances/a.jpg')
        );
    }

    public function test_write_options_carry_the_cache_control_header(): void
    {
        config(['media.cache_control' => 'public, max-age=31536000, immutable']);

        $this->assertSame(
            ['CacheControl' => 'public, max-age=31536000, immutable'],
            MediaStorage::writeOptions()
        );
    }

    public function test_write_options_are_empty_when_cache_control_is_disabled(): void
    {
        config(['media.cache_control' => '']);

        $this->assertSame([], MediaStorage::writeOptions());
    }

    public function test_url_is_null_safe(): void
    {
        $this->assertNull(MediaStorage::url(null));
        $this->assertNull(MediaStorage::url(''));
    }

    public function test_delete_removes_the_file_from_both_disks(): void
    {
        config(['media.mirror_disk' => 'r2']);
        $path = MediaStorage::store(UploadedFile::fake()->image('selfie.jpg'), 'patrols', 'patrol.jpg');

        $this->assertTrue(MediaStorage::delete($path));

        Storage::disk('public')->assertMissing($path);
        Storage::disk('r2')->assertMissing($path);
    }

    public function test_delete_is_null_safe_and_reports_missing_files(): void
    {
        $this->assertFalse(MediaStorage::delete(null));
        $this->assertFalse(MediaStorage::delete(''));
        $this->assertFalse(MediaStorage::delete('attendances/never-existed.jpg'));
    }

    public function test_exists_checks_the_primary_disk(): void
    {
        $path = MediaStorage::store(UploadedFile::fake()->image('selfie.jpg'), 'attendances', 'check_in.jpg');

        $this->assertTrue(MediaStorage::exists($path));
        $this->assertFalse(MediaStorage::exists('attendances/other.jpg'));
        $this->assertFalse(MediaStorage::exists(null));
    }
}
