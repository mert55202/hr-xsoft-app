<?php

declare(strict_types=1);

namespace App\Support;

class LookupKind
{
    /**
     * Planın sözlük listesi. Departman kaydı ayrı tabloda durur; burada adı da seçilebilir.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'alt_factor' => 'Alt faktör',
            'bank' => 'Banka',
            'knowledge' => 'Bilgi ve deneyim',
            'gender' => 'Cinsiyet',
            'work_duration' => 'Çalışma süresi',
            'work_type' => 'Çalışma tipi',
            'evaluation' => 'Değerlendirme',
            'department' => 'Departman',
            'language_level' => 'Dil seviyesi',
            'forex' => 'Döviz',
            'license' => 'Ehliyet',
            'education_level' => 'Eğitim durumu',
            'education_group' => 'Eğitim grubu',
            'education_source' => 'Eğitim kaynağı',
            'education_institution' => 'Eğitim kurumu',
            'disability_status' => 'Engel durumu',
            'disability_type' => 'Engel tipi',
            'education_started' => 'Eğitim başladı durumu',
            'certificate' => 'Sertifika',
            'tag' => 'Etiket',
            'event_type' => 'Etkinlik tipi',
            'event_source' => 'Etkinlik kaynağı',
            'document_type' => 'Evrak tipi',
            'reason' => 'Gerekçe',
            'interview_status' => 'Görüşme durumu',
            'illness' => 'Hastalık',
            'job_family' => 'İş ailesi',
            'leave_type' => 'İzin tipi',
            'quality' => 'Kalite',
            'blood_type' => 'Kan grubu',
            'brand' => 'Marka',
            'marital_status' => 'Medeni durum',
            'payment_method' => 'Ödeme biçimi',
            'reward' => 'Ödül',
            'currency' => 'Para birimi',
            'period' => 'Periyot',
            'position_level' => 'Pozisyon kademesi',
            'report_type' => 'Rapor tipi',
            'health_status' => 'Sağlık durumu',
            'process_type' => 'Süreç tipi',
            'request_type' => 'Talep tipi',
            'compensation' => 'Tazminat parametresi',
            'experience' => 'Tecrübe',
            'technology' => 'Teknoloji',
            'test_type' => 'Test tipi',
            'nationality' => 'Uyruk',
            'pay_unit' => 'Ücret birimi',
            'pay_kind' => 'Ücret tipi',
            'product' => 'Ürün',
            'foreign_language' => 'Yabancı dil',
            'kinship' => 'Yakınlık',
            'competency_type' => 'Yetkinlik tipi',
            'archive_reason' => 'Personel arşiv sebebi',
        ];
    }
}
