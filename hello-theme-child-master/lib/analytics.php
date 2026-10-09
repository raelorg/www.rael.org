<?php
/**
 * Analytics & Tracking
 */
class Rael_Analytics
{
  /**
   * Initialize
   */
  function __construct() {
    add_action( 'wp_head', [$this, 'gtm'] );
  }

  /**
   * GTM
   */
  public function gtm() {
    $gtm = <<<EOT
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-58TMKC');</script>
    EOT;

    echo $gtm;
  }
}

new Rael_Analytics();