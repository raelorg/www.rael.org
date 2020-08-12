<?php
/** start - by Kevin
 * 
/**
 * Redirects
 *
 * Provides a place to handle redirects programmatically, which
 * will always be more powerful than any plugin.
 */
class Rael_Redirects {
  public $request_uri;
  public $http_host;
  public $root = 'www.rael.org';

  function __construct() {
    $this->request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null;
    if ( !$this->request_uri ) {
      return;
    }

    $this->http_host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : null;
    if ( !$this->http_host ) {
      return;
    }

    $this->domains();
    $this->pages();
  }


  /**
   * Domains
   */
  public function domains() {

    // FROM => TO
    $rules = [
      "ar.rael.org" => "/ar/",
      "bg.rael.org" => "/bg/",
      "cs.rael.org" => "/cs/",
      "da.rael.org" => "/da/",
      "nl.rael.org" => "/nl/",
      "fr.rael.org" => "/fr/",
      "de.rael.org" => "/de/",
      "he.rael.org" => "/he/",
      "it.rael.org" => "/it/",
      "ja.rael.org" => "/ja/",
      "ko.rael.org" => "/ko/",
      "lt.rael.org" => "/lt/",
      "mn.rael.org" => "/mn/",
      "fa.rael.org" => "/fa/",
      "pl.rael.org" => "/pl/",
      "ro.rael.org" => "/ro/",
      "ru.rael.org" => "/ru/",
      "sk.rael.org" => "/sk/",
      "sl.rael.org" => "/sl/",
      "es.rael.org" => "/es/",
      "sv.rael.org" => "/sv/",
      "th.rael.org" => "/th/",
    ];

    $subdirectory = isset( $rules[$this->http_host] ) ? $rules[$this->http_host] : null;
    if ( !$subdirectory ) {
      return;
    }

    $to = 'https://' . $this->root . $subdirectory . substr($this->request_uri, 1);

    // Current URL path doesn't match a redirect
    if ( !$to ) {
      return;
    }

    // Redirect to new path
    wp_redirect( $to, 302 );
    exit;
  }


  /**
   * Pages
   */
  public function pages() {

    // FROM => TO
    $rules = [
      "/home" => "/",
      "/rael" => "/rael/",
      "/signup.php" => "/downloads/",
      "/fpw.php" => "/downloads/",
      "/videos" => "/",
      "/embassy" => "/embassy/",
      "/seminars/" => "/happiness-academy/",
      "/faq" => "/faq/",
      "/rael/?singer" => "/rael/",
      "/rael/?racer" => "/rael/",
      "/events" => "/events/",
      "/message" => "/extraterrestrials-created-all-life-on-earth/",
      "/contact" => "/contact/",
      "/download.php" => "/downloads/",
      "/shop/showpic.php?id=11&cat=1" => "/shop/",
      "/links" => "/",
      "/raelians" => "/symbol-infinity/",
      "/shop/showpic.php?id=30&cat=6" => "/shop/",
      "/seminars?c=6&s=208" => "/happiness-academy/",
      "/seminars?c=6&s=308" => "/happiness-academy/",
      "/request.php?408" => "/downloads/",
      "/download.php?view.1" => "/downloads/",
      "/~rael/download.php?list.49" => "/downloads/",
      "/~rael/download.php?list.35" => "/downloads/",
      "/~rael/download.php?list.23" => "/downloads/",
      "/~rael/download.php?list.15" => "/downloads/",
      "/download.php?list.45" => "/downloads/",
      "/~rael/download.php?list.45" => "/downloads/",
      "/~rael/download.php?list.40" => "/downloads/",
      "/~rael/download.php?list.9" => "/downloads/",
      "/download.php?list.49" => "/downloads/",
      "/download.php?list.35" => "/downloads/",
      "/download.php?list.51" => "/downloads/",
      "/download.php?view.95" => "/downloads/",
      "/download.php?view.39" => "/downloads/",
      "/termsofuse.php" => "/privacy-policy/",
      "/privacy.php" => "/privacy-policy/",
      "/download.php?list.23" => "/downloads/",
      "/download.php?list.11" => "/downloads/",
      "/download.php?list.20" => "/downloads/",
      "/~rael/download.php?list.25" => "/downloads/",
      "/download.php?list.15" => "/downloads/",
      "/download.php?list.40" => "/downloads/",
      "/download.php?list.66" => "/downloads/",
      "/download.php?list.9" => "/downloads/",
      "/download.php?list.6" => "/downloads/",
      "/download.php?list.38" => "/downloads/",
      "/~rael/download.php?list.17" => "/downloads/",
      "/download.php?view.64" => "/downloads/",
      "/download.php?list.10" => "/downloads/",
      "/~rael/download.php?list.8" => "/downloads/",
      "/download.php?list.26" => "/downloads/",
      "/~rael/download.php?view.2" => "/downloads/",
      "/download.php?view.60" => "/downloads/",
      "/~rael/download.php?list.58" => "/downloads/",
      "/~rael/download.php?list.24" => "/downloads/",
      "/register-event/112" => "/events/",
      "/~rael/download.php?list.60" => "/downloads/",
      "/~rael/download.php?list.32" => "/downloads/",
      "/news.php" => "/events/",
      "/download.php?view.5" => "/downloads/",
      "/download.php?list.2" => "/downloads/",
      "/~rael/download.php?list.13" => "/downloads/",
      "/download.php?list.8" => "/downloads/",
      "/download.php?view.355" => "/downloads/",
      "/~rael/download.php?list.30" => "/downloads/",
      "/download.php?list.17" => "/downloads/",
      "/~rael/download.php?list.59" => "/downloads/",
      "/download.php?list.30" => "/downloads/",
      "/download.php?view.392" => "/downloads/",
      "/download.php?view.182" => "/downloads/",
      "/download.php?view.2" => "/downloads/",
      "/download.php?list.18" => "/downloads/",
      "/download.php?list.5" => "/downloads/",
      "/download.php?list.24" => "/downloads/",
      "/download.php?view.396" => "/downloads/",
      "/download.php?list.25" => "/downloads/",
      "/download.php?list.12" => "/downloads/",
      "/download.php?list.44" => "/downloads/",
      "/download.php?list.59" => "/downloads/",
      "/~rael/download.php?list.36" => "/downloads/",
      "/download.php?list.48" => "/downloads/",
      "/download.php?list.58" => "/downloads/",
      "/download.php?view.68" => "/downloads/",
      "/download.php?list.46" => "/downloads/",
      "/download.php?list.19" => "/downloads/",
      "/download.php?view.27" => "/downloads/",
      "/download.php?list.64" => "/downloads/",
      "/download.php?view.3" => "/downloads/",
      "/download.php?view.371" => "/downloads/",
      "/download.php?view.41" => "/downloads/",
      "/download.php?view.80" => "/downloads/",
      "/download.php?list.60" => "/downloads/",
      "/e107_plugins/raeladdresses_menu/addresses.php" => "/contact/",
      "/download.php?list.32" => "/downloads/",
      "/download.php?view.32" => "/downloads/",
      "/download.php?list.69" => "/downloads/",
      "/download.php?list.75" => "/downloads/",
      "/download.php?list.62" => "/downloads/",
      "/download.php?list.13" => "/downloads/",
      "/download.php?list.21" => "/downloads/",
      "/download.php?list.70" => "/downloads/",
      "/download.php?view.356" => "/downloads/",
      "/download.php?list.36" => "/downloads/",
      "/download.php?view.393" => "/downloads/",
      "/download.php?list.74" => "/downloads/",
      "/download.php?list.37" => "/downloads/",
      "/download.php?list.65" => "/downloads/",
      "/download.php?view.93" => "/downloads/",
      "/e107_plugins/links_page/links.php?cat.1" => "/",
      "/download.php?view.176" => "/downloads/",
      "/download.php?list.77" => "/downloads/",
      "/download.php?list.76" => "/downloads/",
      "/download.php?view.36" => "/downloads/",
      "/download.php?view.353" => "/downloads/",
      "/download.php?view.281" => "/downloads/",
      "/download.php?view.40" => "/downloads/",
      "/e107_plugins/faq/faq.php?cat.1.14" => "/faq/",
      "/download.php?view.206" => "/downloads/",
      "/news.php?item.666" => "/events/",
      "/download.php?view.49" => "/downloads/",
      "/download.php?view.79" => "/downloads/",
      "/download.php?view.70" => "/downloads/",
      "/download.php?list.22" => "/downloads/",
      "/download.php?view.26" => "/downloads/",
      "/download.php?view.361" => "/downloads/",
      "/download.php?view.399" => "/downloads/",
      "/e107_plugins/links_page/links.php?cat.1&" => "/",
      "/raelscience" => "/rael/",
      "/download.php?view.29" => "/downloads/",
      "/e107_plugins/faq/faq.php?cat.1.15" => "/faq/",
      "/download.php?view.12" => "/downloads/",
      "/download.php?view.395" => "/downloads/",
      "/download.php?view.28" => "/downloads/",
      "/download.php?view.142" => "/downloads/",
      "/download.php?view.370" => "/downloads/",
      "/download.php?view.199" => "/downloads/",
      "/e107_plugins/faq/faq.php?cat.1.16" => "/faq/",
      "/download.php?view.17" => "/downloads/",
      "/e107_plugins/faq/faq.php?cat.3.3" => "/faq/",
      "/download.php?view.133" => "/downloads/",
      "/download.php?view.13" => "/downloads/",
      "/etembassyday" => "/embassy/",
      "/download.php?view.411" => "/downloads/",
      "/download.php?view.128" => "/downloads/",
      "/e107_plugins/faq/faq.php?cat.1.8" => "/faq/",
      "/download.php?view.417" => "/downloads/",
      "/download.php?view.202" => "/downloads/",
      "/download.php?view.136" => "/downloads/",
      "/~rael/download.php?view.17" => "/downloads/",
      "/request.php?1" => "/downloads/",
      "/download.php?view.37" => "/downloads/",
      "/rael/?race" => "/rael/",
      "/download.php?view.423" => "/downloads/",
      "/download.php?view.5/" => "/downloads/",
      "/~rael/download.php?list.37" => "/downloads/",
      "/e107_plugins/faq/faq.php?cat.1.18" => "/faq/",
      "/~rael/download.php?view.5" => "/downloads/",
      "/download.php?list.50" => "/downloads/",
      "/download.php?view.414" => "/downloads/",
      "/download.php?list.80" => "/downloads/",
      "/~rael/seminars/?c=4" => "/happiness-academy/",
      "/e107_plugins/faq/faq.php?cat.1.12" => "/faq/",
      "/download.php?view.439" => "/downloads/",
      "/~rael/download.php?view.371" => "/downloads/",
      "/e107_plugins/faq/faq.php?cat.3.16" => "/faq/",
      "/~rael/download.php?view.439" => "/downloads/",
      "/news.php?extend.15.1" => "/events/",
      "/~rael/download.php?view.80" => "/downloads/",
      "/request.php?8" => "/downloads/",
      "/request.php?392" => "/downloads/",
      "/news.php?item.783" => "/events/",
      "/request.php?356" => "/downloads/",
      "/~rael/download.php?view.137" => "/downloads/",
      "/~rael/download.php?view.386" => "/downloads/",
      "/request.php?397" => "/downloads/",
      "/request.php?413" => "/downloads/",
      "/download.php?list.8%3Cbr" => "/downloads/",
      "/request.php?399" => "/downloads/",
      "/~rael/e107_plugins/vstore/showpic.php?id=30&cat=6" => "/store/",
      "/news.php?default.18.2" => "/events/",
      "/news.php?extend.15.2" => "/events/",
      "/print.php?news.785" => "/events/",
      "/racing/" => "/",
      "/request.php?405" => "/downloads/",
      "/request.php?439" => "/downloads/",
      "/request.php?428" => "/downloads/",
      "/print.php?news.783" => "/events/",
      "/rael_content/rael_bio.php?racingrecord" => "/rael/",
      "/download.php?view.1" => "/downloads/",
      "/home" => "/",
      "/raelscience" => "/rael/",
      "/events" => "/events/",
      "/download.php" => "/downloads/",
      "/rael" => "/rael/",
      "/message" => "/extraterrestrials-created-all-life-on-earth/",
      "/embassy" => "/embassy/",
      "/download.php?list.6" => "/downloads/",
      "/rael/?singer" => "/rael/",
      "/faq" => "/faq/",
      "/contact" => "/contact/",
      "/videos" => "/",
      "/rael/?racer" => "/rael/",
      "/fpw.php" => "/",
      "/signup.php" => "/contact/",
      "/download.php?list.11" => "/downloads/",
      "/download.php?list.2" => "/downloads/",
      "/download.php?view.32" => "/downloads/",
      "/news.php?item.666" => "/events/",
      "/~rael/download.php?list.8" => "/downloads/",
      "/download.php?list.8" => "/downloads/",
      "/download.php?view.68" => "/downloads/",
      "/download.php?view.2" => "/downloads/",
      "/~rael/download.php?view.2" => "/downloads/",
      "/request.php?1" => "/downloads/",
      "/download.php?list.49" => "/downloads/",
      "/raelians" => "/rael/",
      "/etembassyday" => "/embassy/",
      "/download.php?view.5" => "/downloads/",
      "/seminars/" => "/happiness-academy/",
      "/download.php?view.3" => "/downloads/",
      "/links" => "/",
      "/download.php?view.182" => "/downloads/",
      "/download.php?view.93" => "/downloads/",
      "/request.php?8" => "/downloads/",
      "/e107_plugins/raeladdresses_menu/addresses.php" => "/contact/",
      "/download.php?view.40" => "/downloads/",
      "/download.php?view.60" => "/downloads/",
      "/download.php?view.202" => "/downloads/",
      "/seminars?c=6&s=308" => "/happiness-academy/",
      "/download.php?view.36" => "/downloads/",
      "/~rael/download.php?list.35" => "/downloads/",
      "/seminars?c=6&s=208" => "/happiness-academy/",
      "/download.php?view.39" => "/downloads/",
      "/~rael/seminars/?c=4" => "/happiness-academy/",
      "/download.php?view.41" => "/downloads/",
      "/~rael/download.php?list.49" => "/downloads/",
      "/e107_plugins/links_page/links.php?cat.1" => "/",
      "/rael/?race" => "/rael/",
      "/~rael/download.php?list.58" => "/downloads/",
      "/download.php?list.58" => "/downloads/",
      "/news.php" => "/events/",
      "/download.php?view.371" => "/downloads/",
      "/e107_plugins/links_page/links.php?cat.1&" => "/",
      "/~rael/download.php?list.15" => "/downloads/",
      "/download.php?view.361" => "/downloads/",
      "/~rael/download.php?list.23" => "/downloads/",
      "/termsofuse.php" => "/privacy-policy/",
      "/e107_plugins/faq/faq.php?cat.1.18" => "/faq/",
      "/download.php?view.26" => "/downloads/",
      "/download.php?view.370" => "/downloads/",
      "/download.php?view.133" => "/downloads/",
      "/download.php?view.393" => "/downloads/",
      "/download.php?view.353" => "/downloads/",
      "/~rael/download.php?list.40" => "/downloads/",
      "/e107_plugins/faq/faq.php?cat.3.16" => "/faq/",
      "/download.php?view.64" => "/downloads/",
      "/~rael/download.php?list.25" => "/downloads/",
      "/download.php?view.392" => "/downloads/",
      "/request.php?245" => "/downloads/",
      "/download.php?list.44" => "/downloads/",
      "/~rael/download.php?list.32" => "/downloads/",
      "/download.php?view.49" => "/downloads/",
      "/e107_plugins/faq/faq.php?cat.3.3" => "/faq/",
      "/download.php?view.176" => "/downloads/",
      "/e107_plugins/faq/faq.php?cat.1.14" => "/faq/",
      "/request.php?356" => "/downloads/",
      "/~rael/download.php?list.30" => "/downloads/",
      "/download.php?view.12" => "/downloads/",
      "/download.php?list.30" => "/downloads/",
      "/download.php?view.95" => "/downloads/",
      "/~rael/download.php?list.9" => "/downloads/",
      "/download.php?view.199" => "/downloads/",
      "/register-event/112" => "/events/",
      "/~rael/download.php?list.45" => "/downloads/",
      "/~rael/download.php?view.17" => "/downloads/",
      "/download.php?view.396" => "/downloads/",
      "/racing/" => "/",
      "/download.php?view.142" => "/downloads/",
      "/~rael/download.php?list.17" => "/downloads/",
      "/download.php?view.13" => "/downloads/",
      "/download.php?list.32" => "/downloads/",
      "/news.php?item.783" => "/news/",
      "/download.php?view.17" => "/downloads/",
      "/shop/showpic.php?id=11&cat=1" => "/shop/",
      "/download.php?list.20" => "/downloads/",
      "/download.php?view.399" => "/downloads/",
      "/request.php?413" => "/downloads/",
      "/download.php?list.35" => "/downloads/",
      "/download.php?view.356" => "/downloads/",
      "/download.php?view.411" => "/downloads/",
      "/e107_plugins/faq/faq.php?cat.1.16" => "/faq/",
      "/request.php?399" => "/downloads/",
      "/~rael/download.php?view.439" => "/downloads/",
      "/e107_plugins/faq/faq.php?cat.1.8" => "/faq/",
      "/download.php?view.70" => "/downloads/",
      "/download.php?view.79" => "/downloads/",
      "/download.php?view.37" => "/downloads/",
      "/shop/showpic.php?id=30&cat=6" => "/shop/",
      "/download.php?view.355" => "/downloads/",
      "/request.php?439" => "/downloads/",
      "/~rael/download.php?view.137" => "/downloads/",
      "/download.php?view.5/" => "/downloads/",
      "/download.php?view.29" => "/downloads/",
      "/download.php?view.80" => "/downloads/",
      "/privacy.php" => "/privacy-policy/",
      "/download.php?view.395" => "/downloads/",
      "/download.php?list.15" => "/downloads/",
    ];

    $from = str_replace( "https://{$this->http_host}", '', $this->request_uri );
    $to = isset( $rules[$from] ) ? $rules[$from] : null;

    // Current URL path doesn't match a redirect
    if ( !$to ) {
      return;
    }

    // Redirect to new path
    wp_redirect( $to, 302 );
    exit;
  }
}

new Rael_Redirects();

/** end - by Kevin