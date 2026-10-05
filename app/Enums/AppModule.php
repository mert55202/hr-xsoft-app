<?php

declare(strict_types=1);

namespace App\Enums;

enum AppModule: string
{
    case Leave = 'leave';
    case Payroll = 'payroll';
    case Survey = 'survey';
    case Training = 'training';
    case Event = 'event';
    case Request = 'request';
    case Report = 'report';
    case Announcement = 'announcement';
    case Meal = 'meal';
    case Competency = 'competency';
    case Task = 'task';
    case JobProfile = 'job_profile';
    case Recruitment = 'recruitment';
    case OrgChart = 'org_chart';

    public function label(): string
    {
        return match ($this) {
            self::Leave => 'İzin',
            self::Payroll => 'Bordro',
            self::Survey => 'Anket',
            self::Training => 'Eğitim',
            self::Event => 'Etkinlik',
            self::Request => 'Talep',
            self::Report => 'Rapor',
            self::Announcement => 'Duyuru',
            self::Meal => 'Yemek',
            self::Competency => 'Yetkinlik',
            self::Task => 'Görev',
            self::JobProfile => 'İş profili',
            self::Recruitment => 'Seçme',
            self::OrgChart => 'Şema',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Leave => 'Günlük ve saatlik talepler.',
            self::Payroll => 'Dönem pusulası ve kesintiler.',
            self::Survey => 'Formlar ve anonim yanıtlar.',
            self::Training => 'Plan ve katılım.',
            self::Event => 'Departman etkinlikleri.',
            self::Request => 'Kendi talebin ve ast özeti.',
            self::Report => 'Departman raporu. Ücret yok.',
            self::Announcement => 'Departman duyuruları.',
            self::Meal => 'Yemek planı.',
            self::Competency => 'Pozisyon yetkinlikleri.',
            self::Task => 'Görev listesi.',
            self::JobProfile => 'İş profilleri.',
            self::Recruitment => 'Bu departmana açılmış adaylar.',
            self::OrgChart => 'Tüm pozisyon ağacı.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Leave => 'bi-calendar2-check',
            self::Payroll => 'bi-cash-stack',
            self::Survey => 'bi-ui-checks',
            self::Training => 'bi-mortarboard',
            self::Event => 'bi-calendar-event',
            self::Request => 'bi-inbox',
            self::Report => 'bi-bar-chart',
            self::Announcement => 'bi-megaphone',
            self::Meal => 'bi-cup-hot',
            self::Competency => 'bi-stars',
            self::Task => 'bi-check2-square',
            self::JobProfile => 'bi-person-badge',
            self::Recruitment => 'bi-person-plus',
            self::OrgChart => 'bi-diagram-3',
        };
    }

    public function grantable(): bool
    {
        return ! in_array($this, [self::Leave, self::Payroll], true);
    }

    /**
     * @return list<self>
     */
    public static function grantableCases(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $module): bool => $module->grantable(),
        ));
    }

    /**
     * @return list<string>
     */
    public static function grantableValues(): array
    {
        return array_map(
            fn (self $module): string => $module->value,
            self::grantableCases(),
        );
    }
}
