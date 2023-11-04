<?php

/**
 * Plugin Name: 사이트빌더 네이버 연동
 * Plugin URI: https://sitebuilder.kr/
 * Description: 네이버 서치어드바이저에서 사이트 소유 확인을 쉽게 할 수 있습니다. 네이버 애널리틱스 코드를 쉽게 삽입할 수 있습니다.
 * Version: 1.2.0
 * Author: 사이트빌더
 * Author URI: https://sitebuilder.kr/
 **/

if (!defined("ABSPATH")) {
  exit;
}

class SBNSV
{
  function __construct()
  {
    add_action("admin_menu", [$this, "admin_link"]);
    add_action("admin_init", [$this, "settings"]);
    add_action("wp_head", [$this, "sbnsv_meta_tag_markup"]);
    add_action("wp_footer", [$this, "sbnsv_code_markup"]);
  }

  // setup admin menu link
  function admin_link()
  {
    add_options_page("사이트 네이버 연동", "사이트 네이버 연동", "manage_options", "naver-site-verification", [$this, "admin_markup"]);
  }

  function admin_markup()
  { ?>
    <div class="wrap">
      <h1>사이트 네이버 연동</h1>
      <form action="options.php" method="POST">
        <?php
        settings_fields("sbnsv");
        do_settings_sections("naver-site-verification");
        submit_button();
        ?>
      </form>
    </div>
  <?php
  }

  // setup the settings page and fields
  function settings()
  {
    add_settings_section("sbnsv_first_section", "사이트 소유 확인키 입력", [$this, "sbnsv_first_section_description"], "naver-site-verification");
    add_settings_field("sbnsv_key", "사이트 소유 확인키", [$this, "key_field_markup"], "naver-site-verification", "sbnsv_first_section");
    register_setting("sbnsv", "sbnsv_key", ["sanitize_callback" => "sanitize_text_field", "default" => null]);

    add_settings_section("sbnsv_second_section", "애널리틱스 코드 입력", [$this, "sbnsv_second_section_description"], "naver-site-verification");
    add_settings_field("sb_naver_analytics_id", "애널리틱스 코드", [$this, "id_field_markup"], "naver-site-verification", "sbnsv_second_section");
    register_setting("sbnsv", "sb_naver_analytics_id", ["sanitize_callback" => "sanitize_text_field", "default" => null]);
  }

  function key_field_markup()
  { ?>
    <input class="regular-text" type="text" name="sbnsv_key" placeholder="네이버가 제공하는 키를 입력하세요" value="<?php echo esc_attr(get_option("sbnsv_key")); ?>">
  <?php
  }

  function sbnsv_first_section_description()
  {
    echo "<p><a href='https://searchadvisor.naver.com/console/board' rel='nofollow'>네이버 서치어드바이저</a>에서 사이트 소유 확인 과정중 <strong>HTML 태그</strong> 옵션을 선택합니다. 제공되는 메타 태그 중에서 'content' 뒤에 오는<br> 'dfbff5fc84e6ba711daf393a019ec1f3056ea0d7'과 같은 문자열을 복사해 넣으십시오.<br>이 설정은 계속 유지해 주세요. 자세한 설정법은 사이트빌더 <a href='https://sitebuilder.kr/%ec%9b%8c%eb%93%9c%ed%94%84%eb%a0%88%ec%8a%a4-%ec%82%ac%ec%9d%b4%ed%8a%b8%eb%a5%bc-%eb%84%a4%ec%9d%b4%eb%b2%84-%ea%b2%80%ec%83%89%ec%97%90-%eb%93%b1%eb%a1%9d%ed%95%98%eb%8a%94-%eb%ac%b4%eb%a3%8c/' target='_blank'>블로그</a>를 참고하세요.</p>";
  }

  function id_field_markup()
  { ?>
    <input type="text" class="regular-text" name="sb_naver_analytics_id" placeholder="네이버 애널리틱스 아이디를 복사해 넣으세요" value="<?php echo esc_attr(get_option("sb_naver_analytics_id")); ?>">
    <?php
  }

  function sbnsv_second_section_description()
  {
    echo "<p><a href='https://analytics.naver.com/management/mysites.html' rel='nofollow'>네이버 애널리틱스</a>에서 우측 상단 기어 아이콘을 클릭합니다. 해당 사이트의 스크립트를 확인하셔서 '1234567890abcde'과 같은 문자열을 복사해 넣으십시오.<br>이 설정은 계속 유지해 주세요. 자세한 설정법은 사이트빌더 <a href='https://sitebuilder.kr/%ec%9b%8c%eb%93%9c%ed%94%84%eb%a0%88%ec%8a%a4%ec%97%90-%eb%84%a4%ec%9d%b4%eb%b2%84-%ec%b6%94%ec%a0%81-%ec%bd%94%eb%93%9c%eb%a5%bc-%ec%82%bd%ec%9e%85%ed%95%98%eb%8a%94-%eb%b0%a9%eb%b2%95/' target='_blank'>블로그</a>를 참고하세요. 로그인한 관리자의 활동은 기록되지 않습니다.</p>";
  }

  // naver site verification meta tag markup
  function sbnsv_meta_tag_markup()
  {
    if (get_option("sbnsv_key") === null || get_option("sbnsv_key") === "") {
      return;
    }

    if (is_front_page() || is_home()) : ?>
      <meta name="naver-site-verification" content="<?php echo esc_attr(get_option("sbnsv_key")); ?>" />
    <?php endif;
  }

  function sbnsv_code_markup()
  {
    if (empty(get_option("sb_naver_analytics_id"))) {
      return;
    }
    $id = esc_attr(get_option("sb_naver_analytics_id"));

    $user = wp_get_current_user();
    $roles = $user->roles;
    if (in_array("administrator", $roles)) {
      return;
    }
    ?>
    <script type="text/javascript" src="//wcs.naver.net/wcslog.js"></script>
    <script type="text/javascript">
      if (!wcs_add) var wcs_add = {};
      wcs_add["wa"] = "<?php echo $id; ?>";
      if (window.wcs) {
        wcs_do();
      }
    </script>
<?php
  }
}
$naver_site_verification = new SBNSV();
