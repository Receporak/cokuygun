## Proje Adı: COKUYGUN

### COKUYGUN - Piyasada çok uyguna ne ararsan burada.

Projem piyasadaki ürünlerin çok uyguna bulabildiğiniz bir pazaryeri projesidir.
Belirli kategorilerde geçerli 3 alana 1 bedava ürün alabilirsiniz.
Aldığınız ürünlerde 2.üründe %50 indirim alabilirsiniz.

## Projeyi çalıştmak için gerekenler:

### Eğer bilgisayarınızda php, mysql, composer, nginx ve symfony kurulumu yapılmış ise

* `symfony server:start`

komutu ile çalıştırabilirsiniz.

### Eğer bilgisayarınız docker ve make kurulumu yapılmış ise

- `make local_build_first` ile proje sıfırdan kurularak tüm ayarları yapılır ve oluşturulur.
- `make local_rebuild` ile plugin veya ekstra şeyler eklenmişse sıfırdan kurmadan yeniden başlatabilirsiniz
- `make local_remove_container` ile projeyi tamamen silebilirsiniz.
- **ÖNEMLİ NOT** '`local_remove_container`' komutu docker tarafında `aktif olmayan` tüm image ve containerları silecektir.

### Proje oluşturulduktan sonra 
 - Projeye erişmek için:
   - Docker ile oluşturduysanız
     - http://localhost:7001/ ya da http://127.0.0.1:7001/
   - Symfony ile oluşturduysanız
     - http://localhost:8000/ 

 - Projeyi eğer **Docker** ile çalıştırdıysanız direk olarak veritabanı ve kullanıcılar olacaktır.
 - Projeyi eğer **Symfony** ile çalıştırdıysanız veritabanı ve kullanıcılar için sırayla şu işlemler yapılmalıdır.
   - php bin/console d:d:c
   - php bin/console d:s:u --force
   - php bin/console d:m:m -> işlemi yapılırken çıkan uyarıya yes diyin.

### Proje giriş bilgileri:

- **Admin kullanıcı bilgileri:**
    - Kullanıcı adı: admin@cokuygun.com
    - Şifre: 0123456
  
- **Müşteri kullanıcı bilgileri:**
  - Kullanıcı adı: user@cokuygun.com
  - Şifre: 0123456

## Proje Hakkında
 - Projeyi çalıştırdığınızda boş bir anasayfa ile karşılaşılacaksınız.
 - Ürün oluşturmak için admin kullanıcısı ile giriş yapın ve Admin Paneline Git butonuna tıklayın.
 - Giriş yaptıktan sonra sırasyla kategori ve ürün oluşturun.
   - Kategori oluştururken eğer bir üst kategori seçerseniz kategoriniz o kategorinin alt kategorilerine eklenecektir.
 - Ürün oluştururken eğer bir alt kategori seçerseniz o kategorinin üst kategorileri de otomatik olarak ürüne bağlanır.
 - Ürünler oluşturulduktan sonra ürünleri görüntülemek için Front anasayfaya yani http://localhost:7001/ ya da http://127.0.0.1:7001/ 
eğer symfony server:start ile çalıştırdıysanız http://localhost:8000/ adresinden ürünleri görebilirsiniz.
 - Tüm ürünleri görüntülemek için anasayfadaki Tümünü Gör butonuna tıklayın.
 - Ürünlerin alt kategorilerini görüntülemek için açılan ürünler sayfasındaki sol menüdeki ana kategorilere tıklayarak alt kategorilere ulaşabilirsiniz
 - Ürünleri sepete eklemek için giriş yapmanız gerekmektedir.
 - Siparişi tamamlamak için sepet ekranındaki sipariş ver butonuna tıkladıktan sonra adresinizi girerek siparişi tamamlayabilirsiniz.
