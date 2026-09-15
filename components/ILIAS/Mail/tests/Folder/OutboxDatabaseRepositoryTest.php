<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Data\Clock\ClockFactory;
use ILIAS\Data\Clock\ClockInterface;
use ILIAS\Mail\Folder\MailFolderType;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\Mail\Folder\OutboxDatabaseRepository;

class OutboxDatabaseRepositoryTest extends ilMailBaseTestCase
{
    private MockObject&ilDBInterface $mock_database;
    private MockObject&ilMail $mock_mail;
    private MockObject&ClockFactory $mock_clock;
    private MockObject&ClockInterface $mock_utc_clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock_database = $this->createMock(ilDBInterface::class);
        $this->mock_mail = $this->createMock(ilMail::class);
        $this->mock_clock = $this->createMock(ClockFactory::class);
        $this->mock_utc_clock = $this->createMock(ClockInterface::class);
        $this->mock_clock->method('utc')->willReturn($this->mock_utc_clock);
        $this->mock_utc_clock->method('now')->willReturn(new DateTimeImmutable('2026-09-15 12:00:00', new DateTimeZone('UTC')));
    }

    public function testGetOutboxMailsYieldsDueMailWithOwnerId(): void
    {
        $statement = $this->createMock(ilDBStatement::class);
        $row = [
            'mail_id' => 42,
            'user_id' => 1575617,
            'rcp_to' => 'recipient',
            'rcp_cc' => '',
            'rcp_bcc' => '',
            'm_subject' => 'Subject',
            'm_message' => 'Body',
            'attachments' => [],
            'use_placeholders' => 0,
            'schedule_datetime' => '2026-09-04 08:00:00',
            'schedule_timezone' => 'Europe/Berlin',
        ];

        $this->mock_database->expects($this->once())->method('queryF')->with(
            $this->stringContains('schedule_datetime <= %s'),
            ['text', 'text'],
            [MailFolderType::OUTBOX->value, '2026-09-16 12:00:00']
        )->willReturn($statement);

        $this->mock_database->expects($this->exactly(2))->method('fetchAssoc')->with($statement)->willReturnOnConsecutiveCalls(
            $row,
            null
        );

        $this->mock_mail->expects($this->exactly(2))->method('fetchMailData')->willReturnCallback(
            static fn (?array $row): ?array => $row
        );

        $repository = new OutboxDatabaseRepository($this->mock_database, $this->mock_clock, $this->mock_mail);
        $mails = iterator_to_array($repository->getOutboxMails());

        $this->assertCount(1, $mails);
        $this->assertSame(42, $mails[0]->getInternalMailId());
        $this->assertSame(1575617, $mails[0]->getUserId());
    }

    public function testGetOutboxMailsSkipsFutureScheduledMail(): void
    {
        $statement = $this->createMock(ilDBStatement::class);
        $row = [
            'mail_id' => 99,
            'user_id' => 100,
            'rcp_to' => 'recipient',
            'rcp_cc' => '',
            'rcp_bcc' => '',
            'm_subject' => 'Subject',
            'm_message' => 'Body',
            'attachments' => [],
            'use_placeholders' => 0,
            'schedule_datetime' => '2026-09-20 08:00:00',
            'schedule_timezone' => 'Europe/Berlin',
        ];

        $this->mock_database->expects($this->once())->method('queryF')->willReturn($statement);
        $this->mock_database->expects($this->exactly(2))->method('fetchAssoc')->with($statement)->willReturnOnConsecutiveCalls(
            $row,
            null
        );
        $this->mock_mail->expects($this->exactly(2))->method('fetchMailData')->willReturnCallback(
            static fn (?array $row): ?array => $row
        );

        $repository = new OutboxDatabaseRepository($this->mock_database, $this->mock_clock, $this->mock_mail);
        $mails = iterator_to_array($repository->getOutboxMails());

        $this->assertCount(0, $mails);
    }
}
