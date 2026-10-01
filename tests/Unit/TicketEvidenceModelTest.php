<?php

namespace Tests\Unit;

use App\Models\TicketEvidence;
use Tests\TestCase;

class TicketEvidenceModelTest extends TestCase
{
    /** @test */
    public function public_url_returns_evidences_view_for_filesystem_and_custom_disks()
    {
        $filesystem = new TicketEvidence();
        $filesystem->evidence_id = 101;
        $filesystem->storage_disk = 'filesystem';
        $filesystem->file_path = '101/file.pdf';

        $custom = new TicketEvidence();
        $custom->evidence_id = 102;
        $custom->storage_disk = 'custom';
        $custom->file_path = '102/file.pdf';

        $this->assertSame(route('evidences.view', 101), $filesystem->public_url);
        $this->assertSame(route('evidences.view', 102), $custom->public_url);
    }

    /** @test */
    public function public_url_returns_inline_route_for_richtext_filesystem_disk()
    {
        $evidence = new TicketEvidence();
        $evidence->evidence_id = 201;
        $evidence->storage_disk = 'richtext_filesystem';
        $evidence->file_path = '201/image.png';

        $this->assertSame(route('evidences.inline', 201), $evidence->public_url);
    }

    /** @test */
    public function public_url_returns_external_url_when_available()
    {
        $evidence = new TicketEvidence();
        $evidence->storage_disk = 'google_drive';
        $evidence->external_url = 'https://drive.google.com/file/d/abc/view';
        $evidence->file_path = null;

        $this->assertSame('https://drive.google.com/file/d/abc/view', $evidence->public_url);
    }

    /** @test */
    public function public_url_returns_evidences_view_for_public_disk_with_path()
    {
        $evidence = new TicketEvidence();
        $evidence->evidence_id = 301;
        $evidence->storage_disk = 'public';
        $evidence->file_path = '301/file.pdf';

        $this->assertSame(route('evidences.view', 301), $evidence->public_url);
    }

    /** @test */
    public function public_url_returns_null_when_no_valid_source_is_available()
    {
        $evidence = new TicketEvidence();
        $evidence->storage_disk = 'unknown';
        $evidence->file_path = null;
        $evidence->external_url = null;

        $this->assertNull($evidence->public_url);
    }
}
