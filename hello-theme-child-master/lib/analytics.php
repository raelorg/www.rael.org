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
    add_action( 'wp_head', [$this, 'facebook_pixel'] );
    add_action( 'wp_head', [$this, 'gtm'] );
  }

  /**
   * Facebook Pixel
   */
  public function facebook_pixel() {
    ?>
    <!-- Facebook Pixel Code for India72aH -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
          n.callMethod.apply(n,arguments):n.queue.push(arguments)};
          if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
          n.queue=[];t=b.createElement(e);t.async=!0;
          t.src=v;s=b.getElementsByTagName(e)[0];
          s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
          fbq('init', '2064272210260681');
          fbq('track', 'PageView');
      </script>
      <noscript>
        <img height="1" width="1" style="display:none"
          src=https://www.facebook.com/tr?id=2064272210260681&ev=PageView&noscript=1
        />
      </noscript>
      <!-- End Facebook Pixel Code for India72aH -->
      <?php
    }

  /**
   * GTM
   */
  public function gtm() {
    $gtm = <<<EOT
    <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-58TMKC"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
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