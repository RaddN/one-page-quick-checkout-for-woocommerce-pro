const fs = require("fs");
const path = require("path");
const { execFileSync } = require("child_process");
const { chromium } = require("playwright");
const { PNG } = require("pngjs");
const { GIFEncoder, quantize, applyPalette } = require("gifenc");

const siteUrl = "http://plugincy-on-page-checkout.local";
const adminUrl = `${siteUrl}/wp-admin/admin.php?page=onepaqucpro_cart`;
const outputDir = path.join(__dirname, "floating-cart-gif");
const gifPath = path.join(outputDir, "floating-cart-admin-visual-editor.gif");
const posterPath = path.join(outputDir, "floating-cart-admin-visual-editor-poster.png");
const wpLoadPath = path.resolve(__dirname, "../../../../..", "wp-load.php");
const adminUser = process.env.AIOC_ADMIN_USER;
const adminPassword = process.env.AIOC_ADMIN_PASSWORD;

const viewport = { width: 935, height: 988 };
const frameDelay = 105;

const englishLabels = {
  your_cart: "My Cart",
  txt_Select_All: "Select all",
  txt_Selected: "selected",
  rmenu_floating_cart_empty_title: "Your cart is empty",
  rmenu_floating_cart_shop_button_text: "Continue shopping",
  rmenu_floating_cart_coupon_title: "Have a promo code?",
  rmenu_floating_cart_coupon_placeholder: "Enter code",
  rmenu_floating_cart_coupon_button_text: "Apply",
  rmenu_floating_cart_variation_update_text: "Update",
  rmenu_floating_cart_variation_cancel_text: "Cancel",
  txt_you_may_like: "You may also like",
  rmenu_floating_cart_related_add_to_cart_text: "Add to cart",
  txt_subtotal: "Subtotal",
  rmenu_floating_cart_discount_label: "Discount",
  rmenu_floating_cart_shipping_options_label: "Shipping options",
  rmenu_floating_cart_shipping_label: "Shipping",
  rmenu_floating_cart_tax_label: "Tax",
  txt_total: "Total",
  txt_checkout: "Proceed to checkout",
};

const chineseLabels = {
  your_cart: "我的购物车",
  txt_Select_All: "全选",
  txt_Selected: "已选择",
  rmenu_floating_cart_empty_title: "购物车是空的",
  rmenu_floating_cart_shop_button_text: "继续购物",
  rmenu_floating_cart_coupon_title: "有优惠码吗？",
  rmenu_floating_cart_coupon_placeholder: "输入优惠码",
  rmenu_floating_cart_coupon_button_text: "应用",
  rmenu_floating_cart_variation_update_text: "更新",
  rmenu_floating_cart_variation_cancel_text: "取消",
  txt_you_may_like: "你可能也喜欢",
  rmenu_floating_cart_related_add_to_cart_text: "加入购物车",
  txt_subtotal: "小计",
  rmenu_floating_cart_discount_label: "折扣",
  rmenu_floating_cart_shipping_options_label: "配送选项",
  rmenu_floating_cart_shipping_label: "配送费",
  rmenu_floating_cart_tax_label: "税费",
  txt_total: "总计",
  txt_checkout: "去结账",
};

const arabicLabels = {
  your_cart: "سلة التسوق",
  txt_Select_All: "تحديد الكل",
  txt_Selected: "محدد",
  rmenu_floating_cart_empty_title: "سلة التسوق فارغة",
  rmenu_floating_cart_shop_button_text: "متابعة التسوق",
  rmenu_floating_cart_coupon_title: "هل لديك رمز خصم؟",
  rmenu_floating_cart_coupon_placeholder: "أدخل الرمز",
  rmenu_floating_cart_coupon_button_text: "تطبيق",
  rmenu_floating_cart_variation_update_text: "تحديث",
  rmenu_floating_cart_variation_cancel_text: "إلغاء",
  txt_you_may_like: "قد يعجبك أيضا",
  rmenu_floating_cart_related_add_to_cart_text: "أضف إلى السلة",
  txt_subtotal: "المجموع الفرعي",
  rmenu_floating_cart_discount_label: "الخصم",
  rmenu_floating_cart_shipping_options_label: "خيارات الشحن",
  rmenu_floating_cart_shipping_label: "الشحن",
  rmenu_floating_cart_tax_label: "الضريبة",
  txt_total: "الإجمالي",
  txt_checkout: "إتمام الشراء",
};

function ensureDir(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

function getLocalMysqlPort() {
  const sitesPath = process.env.APPDATA ? path.join(process.env.APPDATA, "Local", "sites.json") : "";
  if (!sitesPath || !fs.existsSync(sitesPath)) {
    return "";
  }

  try {
    const sites = JSON.parse(fs.readFileSync(sitesPath, "utf8"));
    const wpRoot = path.dirname(wpLoadPath).toLowerCase();
    const site = Object.values(sites).find((candidate) => {
      const sitePath = candidate && candidate.path ? path.resolve(candidate.path, "app", "public").toLowerCase() : "";
      return candidate && candidate.domain === new URL(siteUrl).hostname && sitePath === wpRoot;
    }) || Object.values(sites).find((candidate) => candidate && candidate.domain === new URL(siteUrl).hostname);

    return site && site.services && site.services.mysql && site.services.mysql.ports && site.services.mysql.ports.MYSQL
      ? String(site.services.mysql.ports.MYSQL[0] || "")
      : "";
  } catch (error) {
    return "";
  }
}

function getLocalAdminCookies() {
  if (!fs.existsSync(wpLoadPath)) {
    return [];
  }

  const host = new URL(siteUrl).hostname;
  const mysqlPort = getLocalMysqlPort();
  const phpCode = `
$wp_load = $argv[1];
$host = $argv[2];
$_SERVER['HTTP_HOST'] = $host;
$_SERVER['SERVER_NAME'] = $host;
$_SERVER['REQUEST_URI'] = '/wp-admin/';
$_SERVER['HTTPS'] = 'off';
define('WP_USE_THEMES', false);
ob_start();
require $wp_load;
ob_end_clean();
$users = get_users(array('role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC'));
if (empty($users)) {
    fwrite(STDERR, 'No administrator user found.');
    exit(2);
}
$user = $users[0];
$expiration = time() + HOUR_IN_SECONDS;
$scheme = is_ssl() ? 'secure_auth' : 'auth';
$auth_name = $scheme === 'secure_auth' ? SECURE_AUTH_COOKIE : AUTH_COOKIE;
$auth_cookie = wp_generate_auth_cookie($user->ID, $expiration, $scheme);
$logged_in_cookie = wp_generate_auth_cookie($user->ID, $expiration, 'logged_in');
$cookies = array(
    array('name' => $auth_name, 'value' => $auth_cookie, 'domain' => $host, 'path' => ADMIN_COOKIE_PATH, 'expires' => $expiration, 'httpOnly' => true, 'secure' => false, 'sameSite' => 'Lax'),
    array('name' => $auth_name, 'value' => $auth_cookie, 'domain' => $host, 'path' => PLUGINS_COOKIE_PATH, 'expires' => $expiration, 'httpOnly' => true, 'secure' => false, 'sameSite' => 'Lax'),
    array('name' => LOGGED_IN_COOKIE, 'value' => $logged_in_cookie, 'domain' => $host, 'path' => COOKIEPATH, 'expires' => $expiration, 'httpOnly' => true, 'secure' => false, 'sameSite' => 'Lax'),
);
if (SITECOOKIEPATH !== COOKIEPATH) {
    $cookies[] = array('name' => LOGGED_IN_COOKIE, 'value' => $logged_in_cookie, 'domain' => $host, 'path' => SITECOOKIEPATH, 'expires' => $expiration, 'httpOnly' => true, 'secure' => false, 'sameSite' => 'Lax');
}
echo wp_json_encode($cookies);
`;

  try {
    const phpArgs = mysqlPort ? ["-d", "mysqli.default_port=" + mysqlPort, "-r", phpCode, wpLoadPath, host] : ["-r", phpCode, wpLoadPath, host];
    const output = execFileSync("php", phpArgs, {
      encoding: "utf8",
      timeout: 15000,
    }).trim();

    return JSON.parse(output);
  } catch (error) {
    return [];
  }
}

async function useLocalAdminCookies(page) {
  const cookies = getLocalAdminCookies();
  if (!cookies.length) {
    return false;
  }

  await page.context().addCookies(cookies);
  return true;
}

async function loginIfNeeded(page) {
  if (!adminUser || !adminPassword) {
    await useLocalAdminCookies(page);
  }

  await page.goto(adminUrl, { waitUntil: "domcontentloaded", timeout: 30000 });

  if ((await page.locator("#user_login").count()) === 0) {
    return;
  }

  if (!adminUser || !adminPassword) {
    throw new Error("Set AIOC_ADMIN_USER and AIOC_ADMIN_PASSWORD to regenerate the admin GIF.");
  }

  await page.fill("#user_login", adminUser);
  await page.fill("#user_pass", adminPassword);
  await Promise.all([
    page.waitForNavigation({ waitUntil: "domcontentloaded", timeout: 30000 }),
    page.click("#wp-submit"),
  ]);
  await page.goto(adminUrl, { waitUntil: "domcontentloaded", timeout: 30000 });
}

async function prepareAdminPresentation(page) {
  await page.waitForSelector(".tab[data-tab='9']", { timeout: 15000 });
  await page.click(".tab[data-tab='9']");
  await page.waitForSelector("#onepaqucpro-floating-cart-editor .onepaqucpro-floating-preview-stage", {
    timeout: 15000,
  });

  await page.evaluate(() => {
    const editor = document.getElementById("onepaqucpro-floating-cart-editor");
    const left = editor.querySelector(".onepaqucpro-floating-editor-left");
    const right = editor.querySelector(".onepaqucpro-floating-editor-right");
    const editModal = editor.querySelector(".onepaqucpro-floating-edit-modal");
    const body = document.body;
    const wpContent = document.getElementById("wpbody-content");

    if (wpContent) {
      Array.from(wpContent.children).forEach((child) => {
        if (!child.classList.contains("tab-container")) {
          child.style.display = "none";
        }
      });
    }

    function setControl(field, value) {
      const control = editor.querySelector(`[data-floating-cart-control="${field}"]`);
      if (!control || control.disabled) {
        return;
      }

      if (control.type === "checkbox") {
        control.checked = value === "1" || value === true;
        control.dispatchEvent(new Event("change", { bubbles: true }));
      } else {
        control.value = value;
        control.dispatchEvent(new Event("input", { bubbles: true }));
        control.dispatchEvent(new Event("change", { bubbles: true }));
      }
    }

    function enableDefaultPreview() {
      [
        "rmenu_enable_sticky_cart",
        "rmenu_floating_cart_show_cart_icon",
        "rmenu_floating_cart_show_cart_count",
        "rmenu_floating_cart_show_select_bar",
        "rmenu_floating_cart_show_item_select",
        "rmenu_floating_cart_show_remove_item",
        "rmenu_floating_cart_show_product_image",
        "rmenu_floating_cart_show_product_title",
        "rmenu_floating_cart_show_product_price",
        "rmenu_floating_cart_show_quantity",
        "rmenu_floating_cart_show_variation_editor",
        "rmenu_floating_cart_show_coupon",
        "rmenu_floating_cart_show_coupon_title",
        "rmenu_floating_cart_show_recommendations",
        "rmenu_floating_cart_show_summary",
        "rmenu_floating_cart_show_subtotal",
        "rmenu_floating_cart_show_discount",
        "rmenu_floating_cart_show_shipping_options",
        "rmenu_floating_cart_show_shipping_total",
        "rmenu_floating_cart_show_tax_total",
        "rmenu_floating_cart_show_total",
        "rmenu_floating_cart_show_checkout",
        "rmenu_floating_cart_show_empty_icon",
        "rmenu_floating_cart_show_shop_button",
      ].forEach((field) => setControl(field, "1"));

      setControl("rmenu_cart_bg_color", "#8b35f4");
      setControl("rmenu_cart_text_color", "#ffffff");
      setControl("rmenu_cart_hover_bg", "#2f63f6");
      setControl("rmenu_cart_hover_text", "#ffffff");
      setControl("rmenu_cart_border_radius", "18px 0 0 18px");
      setControl("rmenu_floating_cart_icon", "shopping-bag");
      setControl("rmenu_floating_cart_empty_icon", "package");
      setControl("rmenu_floating_cart_summary_collapsible", "1");
      setControl("rmenu_floating_cart_summary_initially_collapsed", "1");
      Object.entries({
        your_cart: "My Cart",
        txt_Select_All: "Select all",
        txt_Selected: "selected",
        rmenu_floating_cart_empty_title: "Your cart is empty",
        rmenu_floating_cart_shop_button_text: "Continue shopping",
        rmenu_floating_cart_coupon_title: "Have a promo code?",
        rmenu_floating_cart_coupon_placeholder: "Enter code",
        rmenu_floating_cart_coupon_button_text: "Apply",
        rmenu_floating_cart_variation_update_text: "Update",
        rmenu_floating_cart_variation_cancel_text: "Cancel",
        txt_you_may_like: "You may also like",
        rmenu_floating_cart_related_add_to_cart_text: "Add to cart",
        txt_subtotal: "Subtotal",
        rmenu_floating_cart_discount_label: "Discount",
        rmenu_floating_cart_shipping_options_label: "Shipping options",
        rmenu_floating_cart_shipping_label: "Shipping",
        rmenu_floating_cart_tax_label: "Tax",
        txt_total: "Total",
        txt_checkout: "Proceed to checkout",
      }).forEach(([field, value]) => setControl(field, value));
    }

    enableDefaultPreview();

    const style = document.createElement("style");
    style.id = "floating-cart-admin-gif-style";
    style.textContent = `
      html.wp-toolbar { padding-top: 0 !important; }
      *, *::before, *::after { box-sizing: border-box !important; }
      html, body {
        width: 935px !important;
        height: 988px !important;
        min-width: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
        background: #ffffff !important;
        color: #272a6c !important;
        font-family: Figtree, Inter, "Segoe UI", Arial, sans-serif !important;
        letter-spacing: 0 !important;
      }
      #adminmenumain, #wpadminbar, #screen-meta-links, .notice, .update-nag, .button-row,
      .plugins_banner, .tab-container > .tabs, #wpfooter, .onepaqucpro-floating-editor-left,
      .onepaqucpro-floating-editor-intro, #wpbody-content > h1, #wpbody-content > h2,
      #wpbody-content > .welcome-section, #wpbody-content > .feature-grid,
      #wpbody-content > .plugin-header { display: none !important; }
      #wpcontent, #wpbody, #wpbody-content, .tab-container, .tab-content {
        margin: 0 !important;
        padding: 0 !important;
      }
      #onepaqucpro-floating-cart-editor {
        display: block !important;
        width: 935px !important;
        height: 988px !important;
        margin: 0 !important;
        overflow: hidden !important;
      }
      .admin-gif-stage {
        position: relative;
        width: 935px;
        height: 988px;
        padding: 30px 36px;
        background:
          radial-gradient(circle at 18% 8%, rgba(139, 53, 244, .12), transparent 35%),
          radial-gradient(circle at 90% 88%, rgba(47, 99, 246, .13), transparent 40%),
          #ffffff;
      }
      .admin-gif-shell {
        position: relative;
        width: 100%;
        height: 100%;
        padding: 16px;
        border-radius: 24px;
        background: #efedff;
        box-shadow: 0 34px 80px rgba(39, 42, 108, .15);
        overflow: hidden;
      }
      .admin-gif-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin: 0 0 16px;
        padding: 18px 20px;
        border: 1px solid rgba(139, 53, 244, .14);
        border-radius: 18px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 18px 44px rgba(39, 42, 108, .08);
      }
      .admin-gif-eyebrow {
        display: inline-flex;
        align-items: center;
        height: 26px;
        padding: 0 11px;
        border-radius: 999px;
        color: #8b35f4;
        background: #f3e8ff;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .08em;
      }
      .admin-gif-header h1 {
        margin: 12px 0 7px;
        color: #272a6c;
        font-size: 30px;
        line-height: 1.1;
        letter-spacing: 0;
      }
      .admin-gif-header p {
        max-width: 520px;
        margin: 0;
        color: #63636f;
        font-size: 14px;
        line-height: 1.45;
      }
      .admin-gif-status {
        min-width: 218px;
        padding: 13px 14px;
        border: 1px solid #e6e6ff;
        border-radius: 15px;
        background: #f8f8ff;
      }
      .admin-gif-status strong {
        display: block;
        color: #272a6c;
        font-size: 12px;
        margin-bottom: 7px;
      }
      .admin-gif-status span {
        display: block;
        color: #4d4d5f;
        font-size: 12px;
        line-height: 1.4;
      }
      .admin-gif-main {
        display: grid;
        grid-template-columns: 308px 1fr;
        gap: 16px;
        height: 896px;
      }
      .admin-gif-controls,
      .admin-gif-preview {
        min-width: 0;
        border: 1px solid rgba(139, 53, 244, .13);
        border-radius: 18px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 22px 54px rgba(39, 42, 108, .1);
        overflow: hidden;
      }
      .admin-gif-controls {
        padding: 16px;
      }
      .admin-gif-controls h2 {
        margin: 0 0 14px;
        color: #272a6c;
        font-size: 18px;
        line-height: 1.15;
      }
      .admin-gif-control {
        position: relative;
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr);
        align-items: center;
        gap: 10px;
        margin: 0 0 12px;
        padding: 12px 11px;
        border: 1px solid #e7e9fb;
        border-radius: 13px;
        background: #fbfbff;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
      }
      .admin-gif-control.is-active {
        border-color: #8b35f4;
        background: #fbf7ff;
        box-shadow: 0 0 0 4px rgba(139, 53, 244, .1);
      }
      .admin-gif-icon {
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        color: #fff;
        background: linear-gradient(135deg, #8b35f4, #2f63f6);
        font-size: 16px;
        font-weight: 900;
      }
      .admin-gif-control strong {
        display: block;
        color: #272a6c;
        font-size: 13px;
        margin-bottom: 3px;
      }
      .admin-gif-control > div > span:not(.admin-gif-toggle) {
        display: block;
        color: #686878;
        font-size: 11px;
        line-height: 1.35;
      }
      .admin-gif-toggle {
        width: 42px;
        height: 23px;
        border-radius: 999px;
        background: linear-gradient(135deg, #8b35f4, #2f63f6);
        position: relative;
      }
      .admin-gif-toggle::after {
        content: "";
        position: absolute;
        right: 4px;
        top: 4px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background: #fff;
      }
      .admin-gif-swatches {
        display: flex;
        gap: 8px;
        margin-top: 6px;
      }
      .admin-gif-swatch {
        width: 25px;
        height: 25px;
        border-radius: 999px;
        border: 3px solid #fff;
        box-shadow: 0 0 0 1px #d8def3, 0 6px 15px rgba(39, 42, 108, .13);
      }
      .admin-gif-swatch.is-active {
        box-shadow: 0 0 0 2px #2f63f6, 0 6px 15px rgba(39, 42, 108, .13);
      }
      .admin-gif-checklist {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 14px;
        row-gap: 9px;
        margin-top: 10px;
      }
      .admin-gif-check {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
        color: #58586a;
        font-size: 9.8px;
        line-height: 16px;
        font-weight: 800;
        white-space: nowrap;
      }
      .admin-gif-check::before {
        content: "✓";
        display: grid;
        place-items: center;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        color: #fff;
        background: #8b35f4;
        font-size: 10px;
      }
      .admin-gif-check::before {
        content: "" !important;
        display: inline-block;
        flex: 0 0 16px;
        color: transparent;
      }
      .admin-gif-check::after {
        content: "";
        position: absolute;
        left: 4px;
        top: 5px;
        width: 7px;
        height: 4px;
        border-left: 2px solid #fff;
        border-bottom: 2px solid #fff;
        transform: rotate(-45deg);
      }
      .admin-gif-check.is-off {
        color: #9399ac;
        text-decoration: line-through;
      }
      .admin-gif-check.is-off::before {
        background: #c4cad8;
        box-shadow: inset 0 0 0 1px #a8b0c5;
      }
      .admin-gif-check.is-off::after {
        left: 4px;
        top: 7px;
        width: 8px;
        height: 2px;
        border: 0;
        background: #fff;
        transform: none;
      }
      .admin-gif-check.is-switching::before {
        box-shadow: 0 0 0 4px rgba(139, 53, 244, .16);
      }
      .admin-gif-preview {
        position: relative;
        padding: 16px;
      }
      .onepaqucpro-floating-editor-right {
        position: static !important;
        top: auto !important;
        width: 100% !important;
      }
      .onepaqucpro-floating-preview-toolbar {
        margin: 0 0 12px !important;
        padding: 13px 14px !important;
        border: 1px solid #e8e9f8 !important;
        border-radius: 14px !important;
        background: #fff !important;
        box-shadow: none !important;
      }
      .onepaqucpro-floating-preview-toolbar h3 {
        margin: 0 0 3px !important;
        color: #272a6c !important;
        font-size: 16px !important;
      }
      .onepaqucpro-floating-preview-toolbar p {
        margin: 0 !important;
        color: #686878 !important;
        font-size: 12px !important;
        line-height: 1.35 !important;
      }
      .onepaqucpro-floating-preview-modes {
        background: #f4f4ff !important;
        border-radius: 10px !important;
      }
      .onepaqucpro-floating-preview-modes .button.active {
        color: #2f63f6 !important;
        box-shadow: 0 2px 8px rgba(39, 42, 108, .12) !important;
      }
      .onepaqucpro-floating-preview-stage {
        height: 782px !important;
        min-height: 782px !important;
        padding: 14px !important;
        border: 1px solid #e8e9f8 !important;
        border-radius: 14px !important;
        background: #eef0fb !important;
        overflow: hidden !important;
        box-shadow: none !important;
      }
      .onepaqucpro-floating-preview-button {
        top: 50% !important;
        left: 12px !important;
        transform: translateY(-50%) scale(var(--admin-gif-launcher-scale, 1));
        background: var(--onepaqucpro-floating-primary) !important;
        box-shadow: 0 16px 28px rgba(39, 42, 108, .22) !important;
      }
      .onepaqucpro-floating-preview-drawer {
        width: 100% !important;
        max-width: 326px !important;
        min-height: 748px !important;
        max-height: 748px !important;
        overflow-y: auto !important;
        scrollbar-width: none !important;
        box-shadow: -14px 0 34px rgba(39, 42, 108, .14) !important;
      }
      .onepaqucpro-floating-preview-drawer::-webkit-scrollbar { display: none !important; }
      .onepaqucpro-floating-preview-drawer[data-floating-preview-panel="cart"] {
        padding-bottom: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
      }
      .onepaqucpro-floating-preview-drawer[data-floating-preview-panel][hidden] {
        display: none !important;
      }
      .onepaqucpro-floating-preview-drawer[data-floating-preview-panel="empty"] {
        padding-bottom: 0 !important;
        overflow: hidden !important;
      }
      .admin-gif-cart-scroll-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        scrollbar-width: none;
        padding-bottom: 12px;
      }
      .admin-gif-cart-scroll-body::-webkit-scrollbar {
        display: none !important;
      }
      .admin-gif-sticky-cart-footer {
        flex: 0 0 auto;
        position: relative;
        z-index: 15;
        margin-top: 0;
        padding: 12px 14px 14px;
        background: linear-gradient(180deg, rgba(255, 255, 255, .94), #fff 30%);
        border-top: 1px solid #e8e9f8;
        box-shadow: 0 -18px 32px rgba(39, 42, 108, .12);
      }
      .admin-gif-sticky-cart-footer .onepaqucpro-floating-preview-summary {
        position: relative !important;
        margin: 0 0 10px !important;
        padding: 12px !important;
        border-radius: 11px !important;
        background: #f8f8ff !important;
      }
      .admin-gif-sticky-cart-footer .onepaqucpro-floating-preview-summary.admin-gif-summary-dropup .onepaqucpro-floating-preview-summary-content {
        position: absolute !important;
        left: 0 !important;
        right: 0 !important;
        bottom: calc(100% + 10px) !important;
        z-index: 30 !important;
        display: grid !important;
        max-height: none !important;
        gap: 10px !important;
        padding: 13px !important;
        opacity: 1 !important;
        transform: translateY(0) !important;
        border: 1px solid #e8e9f8 !important;
        border-radius: 12px !important;
        background: #fff !important;
        box-shadow: 0 -18px 34px rgba(39, 42, 108, .16) !important;
        pointer-events: auto !important;
      }
      .admin-gif-sticky-cart-footer .onepaqucpro-floating-preview-summary.admin-gif-summary-dropup .onepaqucpro-floating-preview-summary-content > div {
        display: flex !important;
        justify-content: space-between !important;
        gap: 16px !important;
        color: #4d4d5f !important;
        font-size: 12px !important;
      }
      .admin-gif-sticky-cart-footer .onepaqucpro-floating-preview-summary.admin-gif-summary-dropup .onepaqucpro-floating-preview-summary-toggle span[aria-hidden="true"] {
        transform: rotate(-135deg) !important;
      }
      .admin-gif-sticky-cart-footer .onepaqucpro-floating-preview-summary-toggle {
        cursor: pointer !important;
      }
      .admin-gif-sticky-cart-footer .onepaqucpro-floating-preview-checkout {
        margin: 0 !important;
        width: 100% !important;
        min-height: 40px !important;
        border-radius: 10px !important;
      }
      .onepaqucpro-floating-preview-select {
        margin: 16px 13px !important;
        padding: 12px !important;
      }
      .onepaqucpro-floating-preview-group {
        margin: 12px 13px 0 !important;
      }
      .onepaqucpro-floating-preview-item {
        position: relative !important;
        display: grid !important;
        grid-template-columns: 18px 74px minmax(0, 1fr) 58px !important;
        align-items: start !important;
        gap: 9px !important;
        margin: 12px 13px 0 !important;
        padding: 15px 0 14px !important;
        overflow: visible !important;
      }
      .onepaqucpro-floating-preview-remove {
        top: -2px !important;
        left: 24px !important;
      }
      .onepaqucpro-floating-preview-image {
        width: 72px !important;
        height: 72px !important;
      }
      .onepaqucpro-floating-preview-item-main {
        min-width: 0 !important;
        overflow: visible !important;
      }
      .onepaqucpro-floating-preview-title {
        max-width: 130px !important;
        font-size: 12px !important;
        line-height: 1.35 !important;
        overflow-wrap: anywhere !important;
      }
      .onepaqucpro-floating-preview-meta {
        margin-top: 5px !important;
        font-size: 11px !important;
        line-height: 1.35 !important;
      }
      .onepaqucpro-floating-preview-item > strong {
        align-self: start !important;
        min-width: 58px !important;
        text-align: right !important;
        font-size: 12px !important;
        line-height: 1.3 !important;
        white-space: nowrap !important;
      }
      .onepaqucpro-floating-preview-actions {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        margin-top: 9px !important;
        flex-wrap: nowrap !important;
        overflow: visible !important;
      }
      .onepaqucpro-floating-preview-qty {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        flex: 0 0 auto !important;
      }
      .onepaqucpro-floating-preview-qty button {
        width: 27px !important;
        height: 27px !important;
        min-width: 27px !important;
        min-height: 27px !important;
        padding: 0 !important;
        border-radius: 7px !important;
      }
      .onepaqucpro-floating-preview-qty span {
        display: inline-grid !important;
        place-items: center !important;
        width: 16px !important;
        min-width: 16px !important;
        text-align: center !important;
      }
      .onepaqucpro-floating-preview-variation-editor {
        position: relative !important;
        flex: 0 0 auto !important;
        margin: 0 !important;
        overflow: visible !important;
      }
      .onepaqucpro-floating-preview-variation-toggle {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        min-height: 30px !important;
        padding: 0 !important;
      }
      .onepaqucpro-floating-preview-variation-panel {
        right: -8px !important;
        width: 192px !important;
        z-index: 45 !important;
        padding: 11px !important;
        border-radius: 7px !important;
        box-shadow: 0 16px 30px rgba(39, 42, 108, .16) !important;
      }
      .onepaqucpro-floating-preview-variation-actions {
        display: flex !important;
        flex-wrap: nowrap !important;
      }
      .onepaqucpro-floating-preview-variation-actions button {
        width: auto !important;
      }
      .onepaqucpro-floating-preview-coupon,
      .onepaqucpro-floating-preview-related,
      .onepaqucpro-floating-preview-shipping {
        margin-left: 13px !important;
        margin-right: 13px !important;
      }
      .onepaqucpro-floating-preview-header h2 {
        color: #272a6c !important;
        font-size: 21px !important;
      }
      .onepaqucpro-floating-preview-title,
      .onepaqucpro-floating-preview-related h4,
      .onepaqucpro-floating-preview-summary .total,
      .onepaqucpro-floating-preview-empty-body p {
        color: #272a6c !important;
      }
      .onepaqucpro-floating-preview-stage .admin-gif-typing {
        position: relative !important;
        outline: 3px solid rgba(20, 184, 166, .42) !important;
        outline-offset: 3px !important;
        border-radius: 8px !important;
      }
      .onepaqucpro-floating-preview-stage .admin-gif-typing::after {
        content: "";
        display: inline-block;
        width: 2px;
        height: 1em;
        margin-left: 4px;
        background: #14b8a6;
        vertical-align: -0.14em;
        animation: adminGifCaret .7s steps(2, start) infinite;
      }
      .admin-gif-rtl .onepaqucpro-floating-preview-drawer [data-preview-text] {
        direction: rtl;
        unicode-bidi: plaintext;
      }
      .admin-gif-rtl .onepaqucpro-floating-preview-header,
      .admin-gif-rtl .onepaqucpro-floating-preview-select,
      .admin-gif-rtl .onepaqucpro-floating-preview-coupon,
      .admin-gif-rtl .onepaqucpro-floating-preview-related,
      .admin-gif-rtl .onepaqucpro-floating-preview-shipping,
      .admin-gif-rtl .onepaqucpro-floating-preview-summary,
      .admin-gif-rtl .onepaqucpro-floating-preview-empty-body {
        direction: rtl;
        text-align: right;
      }
      .admin-gif-rtl .onepaqucpro-floating-preview-stage .admin-gif-typing::after {
        margin-left: 0;
        margin-right: 4px;
      }
      .onepaqucpro-floating-edit-modal .admin-gif-modal-field-focus {
        border-color: rgba(20, 184, 166, .45) !important;
        box-shadow: 0 0 0 4px rgba(20, 184, 166, .12) !important;
        background: #f5fffd !important;
      }
      @keyframes adminGifCaret {
        50% { opacity: 0; }
      }
      .onepaqucpro-floating-preview-variation-editor.is-demo-open .onepaqucpro-floating-preview-variation-panel {
        display: grid !important;
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(0) !important;
      }
      .onepaqucpro-floating-preview-variation-editor.is-demo-open .onepaqucpro-floating-preview-variation-toggle {
        box-shadow: 0 0 0 3px rgba(139, 53, 244, .16) !important;
      }
      .onepaqucpro-floating-preview-empty-body {
        min-height: 674px !important;
        padding: 52px 28px !important;
      }
      .onepaqucpro-floating-preview-empty-body p {
        max-width: 220px !important;
        text-align: center !important;
        line-height: 1.35 !important;
      }
      .onepaqucpro-floating-preview-stage [data-preview-target].admin-gif-highlight,
      .onepaqucpro-floating-preview-stage [data-preview-part].admin-gif-highlight {
        outline: 3px solid rgba(139, 53, 244, .34) !important;
        outline-offset: 3px !important;
        border-radius: 10px !important;
      }
      .onepaqucpro-floating-visual-actionbar {
        z-index: 80 !important;
        filter: drop-shadow(0 8px 16px rgba(39, 42, 108, .16));
      }
      .onepaqucpro-floating-visual-actionbar .onepaqucpro-floating-preview-toggle,
      .onepaqucpro-floating-visual-actionbar .onepaqucpro-floating-preview-edit {
        width: 31px !important;
        height: 31px !important;
        min-width: 31px !important;
        min-height: 31px !important;
        border: 2px solid #fff !important;
      }
      .onepaqucpro-floating-visual-actionbar .onepaqucpro-floating-preview-toggle {
        background: #fff !important;
        color: #2f63f6 !important;
      }
      .onepaqucpro-floating-visual-actionbar .onepaqucpro-floating-preview-edit {
        background: #fff !important;
        color: #14a86b !important;
      }
      .onepaqucpro-floating-edit-modal {
        position: fixed !important;
        inset: 0 !important;
        z-index: 100100 !important;
      }
      .onepaqucpro-floating-edit-modal__backdrop {
        background: rgba(39, 42, 108, .34) !important;
        backdrop-filter: blur(3px);
      }
      .onepaqucpro-floating-edit-modal__panel {
        top: 176px !important;
        right: 74px !important;
        width: 342px !important;
        max-height: 694px !important;
        border-radius: 18px !important;
        border: 1px solid #e1e4fb !important;
        box-shadow: 0 30px 80px rgba(39, 42, 108, .28) !important;
        overflow: hidden !important;
      }
      .onepaqucpro-floating-edit-modal__body {
        max-height: 616px !important;
        overflow-y: auto !important;
      }
      .admin-gif-caption {
        display: none !important;
        position: absolute;
        left: 50%;
        bottom: 22px;
        z-index: 50;
        max-width: 690px;
        padding: 13px 20px;
        border-radius: 999px;
        color: #fff;
        background: rgba(39, 42, 108, .94);
        box-shadow: 0 18px 42px rgba(39, 42, 108, .2);
        transform: translateX(-50%);
        font-size: 14px;
        line-height: 1.25;
        font-weight: 900;
        text-align: center;
      }
    `;
    document.head.appendChild(style);

    const stage = document.createElement("div");
    stage.className = "admin-gif-stage";
    stage.innerHTML = `
      <div class="admin-gif-shell">
        <div class="admin-gif-main">
          <div class="admin-gif-controls">
            <h2>Floating Cart Settings</h2>
            <div class="admin-gif-control is-active" data-admin-gif-control="enable">
              <div class="admin-gif-icon">1</div>
              <div><strong>Enable sticky cart</strong><span class="admin-gif-toggle"></span></div>
            </div>
            <div class="admin-gif-control" data-admin-gif-control="style">
              <div class="admin-gif-icon">2</div>
              <div>
                <strong>Colors and button shape</strong>
                <span>Primary, hover, icon style, and radius.</span>
                <div class="admin-gif-swatches">
                  <i class="admin-gif-swatch is-active" data-admin-gif-swatch="0" style="background:linear-gradient(135deg,#8b35f4,#2f63f6)"></i>
                  <i class="admin-gif-swatch" data-admin-gif-swatch="1" style="background:linear-gradient(135deg,#14b8a6,#2563eb)"></i>
                  <i class="admin-gif-swatch" data-admin-gif-swatch="2" style="background:linear-gradient(135deg,#f97316,#ef4444)"></i>
                </div>
              </div>
            </div>
            <div class="admin-gif-control" data-admin-gif-control="elements">
              <div class="admin-gif-icon">3</div>
              <div>
                <strong>Drawer elements</strong>
                <span>Choose exactly what appears in the side cart.</span>
                <div class="admin-gif-checklist">
                  <span class="admin-gif-check" data-admin-gif-check="rmenu_floating_cart_show_product_image">Image</span>
                  <span class="admin-gif-check" data-admin-gif-check="rmenu_floating_cart_show_quantity">Quantity</span>
                  <span class="admin-gif-check" data-admin-gif-check="rmenu_floating_cart_show_coupon">Coupon</span>
                  <span class="admin-gif-check" data-admin-gif-check="rmenu_floating_cart_show_summary">Summary</span>
                </div>
              </div>
            </div>
            <div class="admin-gif-control" data-admin-gif-control="text">
              <div class="admin-gif-icon">4</div>
              <div><strong>Editable labels</strong><span>Cart title, coupon text, checkout button, and empty cart copy.</span></div>
            </div>
            <div class="admin-gif-control" data-admin-gif-control="empty">
              <div class="admin-gif-icon">5</div>
              <div><strong>Cart and empty states</strong><span>Preview both shopper states from the same admin editor.</span></div>
            </div>
          </div>
          <div class="admin-gif-preview" data-admin-gif-preview-slot></div>
        </div>
        <div class="admin-gif-caption" data-admin-gif-caption>Visual admin editor for a customizable floating side cart</div>
      </div>
    `;

    editor.replaceChildren(stage);
    stage.querySelector("[data-admin-gif-preview-slot]").appendChild(right);
    if (left) {
      editor.appendChild(left);
    }
    if (editModal) {
      editor.appendChild(editModal);
    }

    function ensureStickyFooter() {
      const cartPanel = stage.querySelector('.onepaqucpro-floating-preview-drawer[data-floating-preview-panel="cart"]');
      if (!cartPanel || cartPanel.querySelector(".admin-gif-sticky-cart-footer")) {
        return;
      }

      const header = cartPanel.querySelector(".onepaqucpro-floating-preview-header");
      const summary = cartPanel.querySelector(".onepaqucpro-floating-preview-summary");
      const checkout = cartPanel.querySelector(".onepaqucpro-floating-preview-checkout");
      if (!header || !summary || !checkout) {
        return;
      }

      const bodyWrap = document.createElement("div");
      bodyWrap.className = "admin-gif-cart-scroll-body";

      Array.from(cartPanel.childNodes).forEach((node) => {
        if (node === header || node === summary || node === checkout) {
          return;
        }

        bodyWrap.appendChild(node);
      });

      const footer = document.createElement("div");
      footer.className = "admin-gif-sticky-cart-footer";
      header.after(bodyWrap);
      cartPanel.appendChild(footer);
      footer.appendChild(summary);
      footer.appendChild(checkout);
    }

    ensureStickyFooter();

    function setDrawerChecklist(field, enabled, switching = false) {
      const check = stage.querySelector(`[data-admin-gif-check="${field}"]`);
      if (!check) {
        return;
      }

      check.classList.toggle("is-off", !enabled);
      check.classList.toggle("is-switching", !!switching);
    }

    function syncDrawerChecklist() {
      [
        "rmenu_floating_cart_show_product_image",
        "rmenu_floating_cart_show_quantity",
        "rmenu_floating_cart_show_coupon",
        "rmenu_floating_cart_show_summary",
      ].forEach((field) => {
        const control = editor.querySelector(`[data-floating-cart-control="${field}"]`);
        const enabled = !control || (control.type === "checkbox" ? control.checked : control.value === "1");
        setDrawerChecklist(field, enabled, false);
      });
    }

    function setDrawerElement(field, enabled, switching = true) {
      setControl(field, enabled ? "1" : "0");
      setDrawerChecklist(field, enabled, switching);
    }

    function setDrawerElementStates(states) {
      Object.entries(states).forEach(([field, enabled]) => {
        setDrawerElement(field, enabled, true);
      });
    }

    function setActiveControl(name) {
      stage.querySelectorAll("[data-admin-gif-control]").forEach((control) => {
        control.classList.toggle("is-active", control.getAttribute("data-admin-gif-control") === name);
      });
    }

    function setActiveSwatch(index) {
      stage.querySelectorAll("[data-admin-gif-swatch]").forEach((swatch) => {
        swatch.classList.toggle("is-active", swatch.getAttribute("data-admin-gif-swatch") === String(index));
      });
    }

    function applyColorTheme(index) {
      const themes = [
        {
          bg: "#8b35f4",
          hover: "#2f63f6",
          icon: "shopping-bag",
          radius: "18px 0 0 18px",
        },
        {
          bg: "#14b8a6",
          hover: "#2563eb",
          icon: "basket",
          radius: "24px 0 0 24px",
        },
        {
          bg: "#f97316",
          hover: "#ef4444",
          icon: "cart",
          radius: "12px 0 0 12px",
        },
      ];
      const theme = themes[index] || themes[0];

      setActiveSwatch(index);
      setControl("rmenu_cart_bg_color", theme.bg);
      setControl("rmenu_cart_hover_bg", theme.hover);
      setControl("rmenu_floating_cart_icon", theme.icon);
      setControl("rmenu_cart_border_radius", theme.radius);
    }

    function setCaption(text) {
      stage.querySelector("[data-admin-gif-caption]").textContent = text;
    }

    function setStepText(text) {
      const stepText = stage.querySelector("[data-admin-gif-step-text]");
      if (stepText) {
        stepText.textContent = text;
      }
    }

    function setLanguageDirection(direction) {
      stage.classList.toggle("admin-gif-rtl", direction === "rtl");
    }

    function applyLabels(labels, direction = "ltr") {
      Object.entries(labels).forEach(([field, value]) => setControl(field, value));
      setLanguageDirection(direction);
    }

    function clearTypingTarget() {
      stage.querySelectorAll(".admin-gif-typing").forEach((node) => node.classList.remove("admin-gif-typing"));
    }

    function setTypingTarget(selector) {
      clearTypingTarget();
      const node = stage.querySelector(selector);
      if (node) {
        node.classList.add("admin-gif-typing");
      }
    }

    function clearHighlights() {
      stage.querySelectorAll(".admin-gif-highlight").forEach((node) => node.classList.remove("admin-gif-highlight"));
      stage.querySelectorAll(".is-demo-open").forEach((node) => node.classList.remove("is-demo-open"));
      stage.querySelectorAll(".admin-gif-summary-dropup").forEach((node) => node.classList.remove("admin-gif-summary-dropup"));
      stage.querySelectorAll(".admin-gif-check.is-switching").forEach((node) => node.classList.remove("is-switching"));
      stage.querySelectorAll(".onepaqucpro-floating-visual-actionbar").forEach((node) => {
        node.hidden = true;
      });
    }

    function highlight(selector) {
      clearHighlights();
      stage.querySelectorAll(selector).forEach((node) => node.classList.add("admin-gif-highlight"));
    }

    function addHighlight(selector) {
      stage.querySelectorAll(selector).forEach((node) => node.classList.add("admin-gif-highlight"));
    }

    function setMode(mode) {
      const button = stage.querySelector(`[data-floating-preview-mode="${mode}"]`);
      if (button) {
        button.click();
      }

      stage.querySelectorAll("[data-floating-preview-mode]").forEach((modeButton) => {
        modeButton.classList.toggle("active", modeButton.getAttribute("data-floating-preview-mode") === mode);
      });
      stage.querySelectorAll("[data-floating-preview-panel]").forEach((panel) => {
        panel.hidden = panel.getAttribute("data-floating-preview-panel") !== mode;
      });
    }

    function scrollDrawer(top) {
      const drawer = stage.querySelector(".onepaqucpro-floating-preview-drawer:not([hidden])");
      if (drawer) {
        const scrollTarget = drawer.querySelector(".admin-gif-cart-scroll-body") || drawer;
        scrollTarget.scrollTop = top;
      }
    }

    function openVariationPanel(open) {
      const variation = stage.querySelector(".onepaqucpro-floating-preview-variation-editor");
      if (variation) {
        variation.classList.toggle("is-demo-open", !!open);
      }
    }

    function setSummaryDropup(open) {
      const summary = stage.querySelector(".admin-gif-sticky-cart-footer .onepaqucpro-floating-preview-summary");
      if (!summary) {
        return;
      }

      summary.classList.toggle("admin-gif-summary-dropup", !!open);
      summary.querySelectorAll("[data-preview-summary-toggle]").forEach((toggle) => {
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
      });
      summary.querySelectorAll("[data-preview-summary-content]").forEach((content) => {
        content.setAttribute("aria-hidden", open ? "false" : "true");
      });
    }

    function showEditorActions(selector) {
      stage.querySelectorAll(".admin-gif-highlight").forEach((node) => node.classList.remove("admin-gif-highlight"));
      const node = stage.querySelector(selector);
      if (!node) {
        return;
      }

      node.classList.add("admin-gif-highlight");
      node.dispatchEvent(new MouseEvent("mouseenter", {
        view: window,
        bubbles: false,
        cancelable: true,
      }));
    }

    function openEditorModal(selector) {
      showEditorActions(selector);
      const editButton = stage.querySelector(".onepaqucpro-floating-visual-actionbar .onepaqucpro-floating-preview-edit:not([hidden])");
      if (editButton) {
        editButton.click();
      }
    }

    function closeEditorModal() {
      const closeButton = editor.querySelector(".onepaqucpro-floating-edit-modal [data-floating-edit-close]");
      if (closeButton) {
        closeButton.click();
      }
      editor.querySelectorAll(".admin-gif-modal-field-focus").forEach((node) => node.classList.remove("admin-gif-modal-field-focus"));
    }

    function focusModalField(field) {
      editor.querySelectorAll(".admin-gif-modal-field-focus").forEach((node) => node.classList.remove("admin-gif-modal-field-focus"));
      const row = editor.querySelector(`.onepaqucpro-floating-edit-modal [data-modal-control-wrap="${field}"]`);
      if (!row) {
        return;
      }

      row.classList.add("admin-gif-modal-field-focus");
      const control = row.querySelector("[data-modal-control-field]");
      if (control && typeof control.focus === "function") {
        control.focus();
      }
    }

    function setModalFieldValue(field, value) {
      const modalControl = editor.querySelector(`.onepaqucpro-floating-edit-modal [data-modal-control-field="${field}"]`);
      if (!modalControl) {
        setControl(field, value);
        return;
      }

      modalControl.value = value;
      modalControl.dispatchEvent(new Event("input", { bubbles: true }));
      modalControl.dispatchEvent(new Event("change", { bubbles: true }));
    }

    syncDrawerChecklist();
    setLanguageDirection("ltr");

    body.setAttribute("data-floating-admin-gif-ready", "1");
    window.__floatingAdminGif = {
      setControl,
      applyLabels,
      applyColorTheme,
      setActiveControl,
      setActiveSwatch,
      setCaption,
      setStepText,
      setLanguageDirection,
      setTypingTarget,
      clearTypingTarget,
      clearHighlights,
      highlight,
      addHighlight,
      setMode,
      scrollDrawer,
      openVariationPanel,
      setSummaryDropup,
      setDrawerElementStates,
      showEditorActions,
      openEditorModal,
      closeEditorModal,
      focusModalField,
      setModalFieldValue,
      ensureStickyFooter,
      enableDefaultPreview,
    };

    window.scrollTo(0, 0);
  });
}

async function captureFrame(page, frames) {
  const png = await page.screenshot({ fullPage: false, type: "png" });
  frames.push(png);
}

async function hold(page, frames, count) {
  for (let i = 0; i < count; i += 1) {
    await captureFrame(page, frames);
  }
}

async function applyStep(page, frames, count, script, arg) {
  await page.evaluate(script, arg);
  await page.waitForTimeout(150);
  await hold(page, frames, count);
}

async function typePreviewText(page, frames, field, text, selector, direction = "ltr") {
  await page.evaluate(({ field: targetField, selector: targetSelector, direction: textDirection }) => {
    window.__floatingAdminGif.setLanguageDirection(textDirection);
    window.__floatingAdminGif.setTypingTarget(targetSelector);
    window.__floatingAdminGif.setControl(targetField, "");
  }, { field, selector, direction });
  await hold(page, frames, 2);

  let value = "";
  for (const char of Array.from(text)) {
    value += char;
    await page.evaluate(({ field: targetField, value: nextValue }) => {
      window.__floatingAdminGif.setControl(targetField, nextValue);
    }, { field, value });
    await page.waitForTimeout(60);
    await hold(page, frames, 1);
  }

  await hold(page, frames, 3);
}

async function typeModalText(page, frames, field, text, direction = "ltr") {
  await page.evaluate(({ field: targetField, direction: textDirection }) => {
    window.__floatingAdminGif.setLanguageDirection(textDirection);
    window.__floatingAdminGif.focusModalField(targetField);
    window.__floatingAdminGif.setModalFieldValue(targetField, "");
  }, { field, direction });
  await hold(page, frames, 2);

  let value = "";
  for (const char of Array.from(text)) {
    value += char;
    await page.evaluate(({ field: targetField, value: nextValue }) => {
      window.__floatingAdminGif.setModalFieldValue(targetField, nextValue);
    }, { field, value });
    await page.waitForTimeout(60);
    await hold(page, frames, 1);
  }

  await hold(page, frames, 3);
}

function encodeGif(frames) {
  const gif = GIFEncoder();

  frames.forEach((png, index) => {
    const image = PNG.sync.read(png);
    const palette = quantize(image.data, 256, { format: "rgb565" });
    const indexed = applyPalette(image.data, palette, "rgb565");
    gif.writeFrame(indexed, image.width, image.height, {
      palette,
      delay: index === 0 ? 750 : frameDelay,
      repeat: 0,
    });
  });

  gif.finish();
  return Buffer.from(gif.bytes());
}

async function main() {
  ensureDir(outputDir);

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport, deviceScaleFactor: 1 });

  await loginIfNeeded(page);
  await prepareAdminPresentation(page);

  const frames = [];

  await applyStep(page, frames, 8, () => {
    window.__floatingAdminGif.enableDefaultPreview();
    window.__floatingAdminGif.ensureStickyFooter();
    window.__floatingAdminGif.setActiveControl("enable");
    window.__floatingAdminGif.setCaption("Enable a floating side cart and preview it directly from the admin screen");
    window.__floatingAdminGif.setStepText("Hover an element to reveal the editor actions: eye for visibility, pen for settings.");
    window.__floatingAdminGif.setMode("cart");
    window.__floatingAdminGif.scrollDrawer(0);
    window.__floatingAdminGif.showEditorActions(".onepaqucpro-floating-preview-button");
  });

  await applyStep(page, frames, 9, () => {
    window.__floatingAdminGif.setActiveControl("style");
    window.__floatingAdminGif.setCaption("Click the pen icon to open element settings in a focused popup");
    window.__floatingAdminGif.setStepText("The popup exposes the controls mapped to the selected cart element.");
    window.__floatingAdminGif.openEditorModal(".onepaqucpro-floating-preview-button");
  });

  await applyStep(page, frames, 6, () => {
    window.__floatingAdminGif.closeEditorModal();
    window.__floatingAdminGif.setActiveControl("style");
    window.__floatingAdminGif.applyColorTheme(1);
    window.__floatingAdminGif.setCaption("Apply a teal brand theme to the floating cart preview");
    window.__floatingAdminGif.setStepText("Color and button settings update the preview instantly.");
    window.__floatingAdminGif.showEditorActions(".onepaqucpro-floating-preview-button");
  });

  await applyStep(page, frames, 6, () => {
    window.__floatingAdminGif.closeEditorModal();
    window.__floatingAdminGif.setActiveControl("style");
    window.__floatingAdminGif.applyColorTheme(2);
    window.__floatingAdminGif.setCaption("Switch to a warm brand color without leaving the visual editor");
    window.__floatingAdminGif.setStepText("Each swatch applies immediately to the launcher and checkout button.");
    window.__floatingAdminGif.showEditorActions(".onepaqucpro-floating-preview-button");
  });

  await applyStep(page, frames, 8, () => {
    window.__floatingAdminGif.closeEditorModal();
    window.__floatingAdminGif.setActiveControl("elements");
    window.__floatingAdminGif.setCaption("Enable or disable drawer elements from the visual editor");
    window.__floatingAdminGif.setStepText("Turn individual parts of the side cart on or off and see the drawer update immediately.");
    window.__floatingAdminGif.scrollDrawer(0);
    window.__floatingAdminGif.showEditorActions(".onepaqucpro-floating-preview-item");
  });

  await applyStep(page, frames, 7, () => {
    window.__floatingAdminGif.setActiveControl("elements");
    window.__floatingAdminGif.setCaption("Disable product image and coupon to simplify the drawer layout");
    window.__floatingAdminGif.setStepText("Two drawer elements are switched off here, and the preview removes them instantly.");
    window.__floatingAdminGif.clearHighlights();
    window.__floatingAdminGif.setDrawerElementStates({
      rmenu_floating_cart_show_product_image: false,
      rmenu_floating_cart_show_coupon: false,
    });
    window.__floatingAdminGif.addHighlight(".onepaqucpro-floating-preview-item");
  });

  await applyStep(page, frames, 7, () => {
    window.__floatingAdminGif.setActiveControl("elements");
    window.__floatingAdminGif.setCaption("Enable those elements again when the cart needs the full shopper view");
    window.__floatingAdminGif.setStepText("The same controls can restore image and coupon sections without leaving the editor.");
    window.__floatingAdminGif.setDrawerElementStates({
      rmenu_floating_cart_show_product_image: true,
      rmenu_floating_cart_show_coupon: true,
    });
    window.__floatingAdminGif.showEditorActions(".onepaqucpro-floating-preview-coupon");
  });

  await applyStep(page, frames, 8, () => {
    window.__floatingAdminGif.closeEditorModal();
    window.__floatingAdminGif.setActiveControl("elements");
    window.__floatingAdminGif.setCaption("Variation switching and quantity controls can be shown inside the drawer");
    window.__floatingAdminGif.setStepText("Advanced cart item controls are part of the visual preview.");
    window.__floatingAdminGif.clearHighlights();
    window.__floatingAdminGif.openVariationPanel(true);
    window.__floatingAdminGif.addHighlight(".onepaqucpro-floating-preview-variation-toggle");
  });

  await applyStep(page, frames, 4, (labels) => {
    window.__floatingAdminGif.closeEditorModal();
    window.__floatingAdminGif.applyLabels(labels, "ltr");
    window.__floatingAdminGif.setActiveControl("text");
    window.__floatingAdminGif.setCaption("Editable Labels can be changed directly in the cart preview");
    window.__floatingAdminGif.setStepText("Typing in visual mode updates the customer-facing cart title as you edit.");
    window.__floatingAdminGif.openVariationPanel(false);
    window.__floatingAdminGif.clearHighlights();
    window.__floatingAdminGif.setMode("cart");
    window.__floatingAdminGif.scrollDrawer(0);
    window.__floatingAdminGif.setTypingTarget('.onepaqucpro-floating-preview-header [data-preview-text="your_cart"]');
  }, englishLabels);

  await typePreviewText(
    page,
    frames,
    "your_cart",
    chineseLabels.your_cart,
    '.onepaqucpro-floating-preview-header [data-preview-text="your_cart"]',
    "ltr"
  );

  await applyStep(page, frames, 11, (labels) => {
    window.__floatingAdminGif.applyLabels(labels, "ltr");
    window.__floatingAdminGif.clearTypingTarget();
    window.__floatingAdminGif.setActiveControl("text");
    window.__floatingAdminGif.setCaption("All editable drawer labels are now previewed in Chinese");
    window.__floatingAdminGif.setStepText("Cart title, coupon copy, totals, recommendations, and checkout text change together.");
    window.__floatingAdminGif.clearHighlights();
    window.__floatingAdminGif.scrollDrawer(0);
    window.__floatingAdminGif.highlight(".onepaqucpro-floating-preview-drawer");
  }, chineseLabels);

  await applyStep(page, frames, 5, () => {
    window.__floatingAdminGif.setActiveControl("text");
    window.__floatingAdminGif.setCaption("The popup editor can change the same labels with focused fields");
    window.__floatingAdminGif.setStepText("Open the coupon text popup and type a new label from the element settings panel.");
    window.__floatingAdminGif.clearHighlights();
    window.__floatingAdminGif.openEditorModal(".onepaqucpro-floating-preview-coupon");
  });

  await typeModalText(page, frames, "rmenu_floating_cart_coupon_title", arabicLabels.rmenu_floating_cart_coupon_title, "rtl");

  await applyStep(page, frames, 10, (labels) => {
    window.__floatingAdminGif.closeEditorModal();
    window.__floatingAdminGif.applyLabels(labels, "rtl");
    window.__floatingAdminGif.clearTypingTarget();
    window.__floatingAdminGif.setActiveControl("text");
    window.__floatingAdminGif.setCaption("After popup editing, every editable drawer label is previewed in Arabic");
    window.__floatingAdminGif.setStepText("Right-to-left labels are shown in the drawer before saving the settings.");
    window.__floatingAdminGif.clearHighlights();
    window.__floatingAdminGif.scrollDrawer(0);
    window.__floatingAdminGif.highlight(".onepaqucpro-floating-preview-drawer");
  }, arabicLabels);

  await applyStep(page, frames, 9, () => {
    window.__floatingAdminGif.closeEditorModal();
    window.__floatingAdminGif.setActiveControl("elements");
    window.__floatingAdminGif.setCaption("Collapsed total and checkout stay in the sticky cart footer");
    window.__floatingAdminGif.setStepText("Click the total row to expand subtotal, discount, shipping, and tax as a drop-up.");
    window.__floatingAdminGif.scrollDrawer(280);
    window.__floatingAdminGif.clearHighlights();
    window.__floatingAdminGif.setSummaryDropup(true);
    window.__floatingAdminGif.addHighlight(".admin-gif-sticky-cart-footer .onepaqucpro-floating-preview-summary-toggle");
  });

  await applyStep(page, frames, 9, () => {
    window.__floatingAdminGif.closeEditorModal();
    window.__floatingAdminGif.setActiveControl("empty");
    window.__floatingAdminGif.setCaption("Preview and customize the empty-cart state before saving");
    window.__floatingAdminGif.setStepText("Empty icon, message, and shop button are part of the same editor.");
    window.__floatingAdminGif.clearHighlights();
    window.__floatingAdminGif.setMode("empty");
    window.__floatingAdminGif.scrollDrawer(0);
    window.__floatingAdminGif.highlight(".onepaqucpro-floating-preview-empty-body");
  });

  await applyStep(page, frames, 9, () => {
    window.__floatingAdminGif.closeEditorModal();
    window.__floatingAdminGif.setActiveControl("style");
    window.__floatingAdminGif.applyColorTheme(0);
    window.__floatingAdminGif.setCaption("A professional floating cart experience that can match the store brand");
    window.__floatingAdminGif.setStepText("Use the admin visual editor to build a branded slide-out cart.");
    window.__floatingAdminGif.clearHighlights();
    window.__floatingAdminGif.setMode("cart");
    window.__floatingAdminGif.scrollDrawer(0);
    window.__floatingAdminGif.highlight(".onepaqucpro-floating-preview-drawer");
  });

  const poster = await page.screenshot({ fullPage: false, type: "png" });
  fs.writeFileSync(posterPath, poster);

  await browser.close();

  fs.writeFileSync(gifPath, encodeGif(frames));

  console.log(JSON.stringify({
    gifPath,
    posterPath,
    frames: frames.length,
    width: viewport.width,
    height: viewport.height,
  }, null, 2));
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
