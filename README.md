# Film & Dizi Öneri Sistemi - Veritabanı Yönetim Sistemleri-II Projesi

Bu proje, **BTS304 - Veritabanı Yönetim Sistemleri-II** dersi kapsamında geliştirilmiş, kullanıcının zevklerine göre kişiselleştirilmiş film ve dizi önerileri sunan bir web uygulamasıdır. Proje, kullanıcıların içerikleri puanlamasına, favori tür ve oyuncularını belirtmesine olanak tanıyarak bu verileri işler ve dinamik olarak öneriler oluşturur.

## Projenin Amacı

Projenin temel amacı, modern veritabanı yönetim sistemlerinin ileri düzey özelliklerini kullanarak (Saklı Yordamlar, Tetikleyiciler, Fonksiyonlar) karmaşık bir iş mantığını veritabanı katmanında verimli bir şekilde yönetmektir. Uygulama, PHP ve MySQL kullanılarak geliştirilmiştir.

## Proje Dosya Yapısı

Proje, aşağıdaki dosya ve klasör yapısına sahiptir:

- **/assets**: CSS, resimler gibi statik dosyaları içerir.
  - **/images**: Projede kullanılan görseller.
  - **/style.css**: Sitenin genel stil dosyası.
- **/cache**: Performansı artırmak için oluşturulan önbellek dosyalarının tutulduğu klasör.
- **config.php**: Veritabanı bağlantı bilgileri, API anahtarları ve genel yapılandırma ayarları.
- **functions.php**: Uygulamanın tüm arka uç mantığını ve veritabanı işlemlerini yürüten fonksiyonlar. (Saklı yordamları çağırır).
- **index.php**: Ana sayfa. Popüler filmleri ve dizileri listeler.
- **login.php, register.php, logout.php**: Kullanıcı giriş, kayıt ve çıkış işlemleri.
- **movies.php, tv_shows.php**: Tüm filmleri ve dizileri gelişmiş filtreleme ve sıralama seçenekleriyle listeleyen sayfalar.
- **movie_detail.php, tv_show_detail.php**: Tek bir film veya dizinin detaylarını gösteren sayfalar.
- **profile.php**: Kullanıcının puanladığı içerikleri ve favori tercihlerini yönettiği profil sayfası.
- **rate_content.php**: Kullanıcının rastgele içerikleri puanlayarak sisteme veri sağladığı sayfa.
- **recommendations.php**: Kullanıcının zevklerine göre kişiselleştirilmiş önerilerin sunulduğu sayfa.
- **search.php**: Arama sonuçlarını listeleyen sayfa.
- **database_setup.sql**: Veritabanı tablolarını oluşturan ana SQL script'i.
- **create_procedures.sql**: Tüm saklı yordamları, tetikleyicileri ve fonksiyonları oluşturan SQL script'i.
- **error.log**: PHP hatalarının kaydedildiği log dosyası.

## Kurulum ve Çalıştırma Adımları

Bu projeyi yerel makinenizde çalıştırmak için aşağıdaki adımları izleyin:

### Gereksinimler
- Bir web sunucusu (Apache önerilir)
- PHP (versiyon 7.4 veya üstü)
- MySQL Veritabanı

### Kurulum

1.  **Veritabanını Oluşturun:**
    MySQL yönetim aracınızda (phpMyAdmin vb.) yeni bir veritabanı oluşturun.
    ```sql
    CREATE DATABASE dizi_film_oneri_sistemi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    ```

2.  **Tabloları ve Yapıyı İçe Aktarın:**
    Oluşturduğunuz bu yeni veritabanını seçin ve sırasıyla aşağıdaki iki SQL dosyasını içe aktarın (import edin):
    - `database_setup.sql` (Önce tabloları oluşturur)
    - `create_procedures.sql` (Sonra yordamları, trigger'ları ve fonksiyonu ekler)
    
    *Not: Bu script'ler ayrıca TMDb'den alınan örnek verileri de içerebilir. Eğer içermiyorsa, sistem boş başlayacaktır.*

3.  **Yapılandırma Dosyasını Düzenleyin:**
    Proje ana dizinindeki `config.php` dosyasını bir metin düzenleyici ile açın ve kendi yerel sunucu bilgilerinize göre güncelleyin:
    ```php
    // Database configuration
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'dizi_film_oneri_sistemi'); // Oluşturduğunuz veritabanının adı
    define('DB_USER', 'root'); // MySQL kullanıcı adınız
    define('DB_PASS', ''); // MySQL şifreniz

    // TMDB API configuration
    define('TMDB_API_KEY', 'BURAYA_KENDI_TMDB_API_ANAHTARINIZI_GIRIN');
    ```
    *TMDB (The Movie Database) sitesinden ücretsiz bir API anahtarı alıp `TMDB_API_KEY` alanına eklemeniz, görsellerin ve bazı verilerin çekilmesi için gereklidir.*
    *Bu adımları atlamak isterseniz `dizi_film_oneri_sistemi.sql` dosyasını direkt olarak içe aktar yaparak bütün veritabanını kullanılabilir halde elde edebilirsiniz.*

5.  **Projeyi Çalıştırın:**
    Proje dosyalarını web sunucunuzun ana dizinine (örn: `htdocs` veya `www`) kopyalayın ve tarayıcınızdan `http://localhost/PROJE_KLASOR_ADI/` adresine gidin.

## Geliştirilen İleri Düzey Veritabanı Özellikleri

Bu projede, dersin gereksinimleri doğrultusunda aşağıdaki ileri düzey veritabanı özellikleri kullanılmıştır:

- **Saklı Yordamlar (Stored Procedures):** Tüm veri ekleme, silme, güncelleme, listeleme ve arama işlemleri (CRUD) PHP'den doğrudan SQL sorguları ile değil, veritabanında saklanan yordamlar (`CALL sp_...`) aracılığıyla gerçekleştirilmiştir. Bu, güvenliği artırır, performansı optimize eder ve iş mantığını merkezileştirir.
- **Tetikleyiciler (Triggers):**
  - Bir kullanıcı silindiğinde, o kullanıcıya ait tüm puanlama ve favori kayıtlarının otomatik olarak silinmesini sağlayan bir trigger.
  - Bir içeriğe yeni puanlama yapıldığında veya puanlama güncellendiğinde, bu değişikliği bir `log` tablosuna kaydeden bir trigger.
- **Kullanıcı Tanımlı Fonksiyon (User-Defined Function):** Bir filme verilen olumlu oy sayısını hesaplayan ve döndüren bir fonksiyon.
