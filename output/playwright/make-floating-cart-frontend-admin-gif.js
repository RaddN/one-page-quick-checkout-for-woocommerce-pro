const fs = require("fs");
const path = require("path");
const { chromium } = require("playwright");
const { PNG } = require("pngjs");
const { GIFEncoder, quantize, applyPalette } = require("gifenc");

const outputDir = path.join(__dirname, "floating-cart-gif");
const gifPath = path.join(outputDir, "floating-cart-frontend-admin-showcase.gif");
const posterPath = path.join(outputDir, "floating-cart-frontend-admin-showcase-poster.png");
const adminGifPath = path.join(outputDir, "floating-cart-admin-visual-editor.gif");

const viewport = { width: 935, height: 988 };
const frameDelay = 105;

function ensureDir(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

function buildHtml(adminGifUrl) {
  return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Floating Cart Frontend and Admin Showcase</title>
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body {
  width: 935px;
  height: 988px;
  margin: 0;
  overflow: hidden;
  background: #f5f7fb;
  color: #151a2d;
  font-family: Inter, "Segoe UI", Arial, sans-serif;
  letter-spacing: 0;
}
button, input, select {
  font: inherit;
  letter-spacing: 0;
}
button { cursor: default; }
.showcase {
  position: relative;
  width: 935px;
  height: 988px;
  overflow: hidden;
  background: #f3f6fb;
}
.front-stage,
.admin-stage {
  position: absolute;
  inset: 0;
  width: 935px;
  height: 988px;
  transition: opacity 420ms ease, transform 420ms ease;
}
.showcase[data-mode="frontend"] .front-stage {
  opacity: 1;
  transform: translateX(0);
  pointer-events: auto;
}
.showcase[data-mode="frontend"] .admin-stage {
  opacity: 0;
  transform: translateX(22px);
  pointer-events: none;
}
.showcase[data-mode="admin"] .front-stage {
  opacity: 0;
  transform: translateX(-22px);
  pointer-events: none;
}
.showcase[data-mode="admin"] .admin-stage {
  opacity: 1;
  transform: translateX(0);
  pointer-events: auto;
}
.store-top {
  position: relative;
  z-index: 5;
  height: 70px;
  display: grid;
  grid-template-columns: 216px minmax(0, 1fr) 174px;
  align-items: center;
  gap: 18px;
  padding: 0 28px;
  background: rgba(255, 255, 255, .96);
  border-bottom: 1px solid #e5eaf3;
  box-shadow: 0 12px 34px rgba(16, 24, 40, .04);
}
.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 900;
  color: #24304f;
  font-size: 20px;
}
.brand-mark {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  background: #6938ef;
  position: relative;
  box-shadow: inset 0 -7px 0 rgba(255,255,255,.18);
}
.brand-mark::before,
.brand-mark::after {
  content: "";
  position: absolute;
  left: 8px;
  right: 8px;
  height: 3px;
  border-radius: 8px;
  background: #fff;
}
.brand-mark::before { top: 8px; }
.brand-mark::after { top: 16px; }
.search {
  height: 36px;
  display: flex;
  align-items: center;
  border: 1px solid #e7ebf3;
  border-radius: 8px;
  background: #f5f7fb;
  overflow: hidden;
}
.search input {
  min-width: 0;
  flex: 1 1 auto;
  height: 100%;
  border: 0;
  outline: 0;
  color: #697188;
  background: transparent;
  padding: 0 14px;
  font-size: 13px;
}
.search button {
  width: 38px;
  height: 100%;
  border: 0;
  color: #fff;
  background: #6938ef;
  display: grid;
  place-items: center;
}
.top-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  color: #667085;
  font-size: 12px;
}
.top-action {
  position: relative;
  width: 32px;
  height: 32px;
  display: grid;
  place-items: center;
  border: 1px solid #e7ebf3;
  border-radius: 8px;
  background: #fff;
  color: #24304f;
  font-weight: 900;
}
.top-action svg,
.search button svg,
.small-tool svg,
.icon-button svg,
.remove-item svg,
.cart-launcher svg,
.location-icon svg {
  width: 18px;
  height: 18px;
  display: block;
}
.cart-action {
  color: #6938ef;
}
.cart-action svg {
  width: 19px;
  height: 19px;
}
.top-dot {
  position: absolute;
  right: -4px;
  top: -5px;
  min-width: 16px;
  height: 16px;
  display: grid;
  place-items: center;
  border-radius: 999px;
  background: #f04438;
  color: #fff;
  font-size: 9px;
  line-height: 1;
}
.nav-row {
  position: relative;
  z-index: 4;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 30px;
  padding: 0 28px;
  background: #fff;
  border-bottom: 1px solid #e7ebf3;
  color: #344054;
  font-size: 13px;
  font-weight: 700;
}
.nav-row span:nth-child(2) { color: #6938ef; }
.shop-shell {
  position: relative;
  width: 100%;
  height: calc(100% - 118px);
  padding: 26px 22px 18px;
}
.shop-toolbar {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 22px;
}
.shop-toolbar h1 {
  margin: 0;
  font-size: 20px;
  line-height: 1.2;
  color: #101828;
}
.filter-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.filter-chip,
.sort-select {
  height: 34px;
  display: inline-flex;
  align-items: center;
  border: 1px solid #e7ebf3;
  border-radius: 8px;
  background: #fff;
  color: #344054;
  padding: 0 12px;
  font-size: 12px;
  font-weight: 800;
  white-space: nowrap;
}
.filter-chip.primary {
  border-color: #6938ef;
  background: #6938ef;
  color: #fff;
}
.product-grid {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px 16px;
}
.product-card {
  min-width: 0;
  height: 310px;
  display: flex;
  flex-direction: column;
  border: 1px solid #e7ebf3;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 14px 30px rgba(19, 31, 57, .07);
  overflow: hidden;
}
.product-media {
  position: relative;
  height: 142px;
  display: grid;
  place-items: center;
  background: #f8fafc;
  border-bottom: 1px solid #eef2f6;
}
.product-art {
  position: relative;
  width: 92px;
  height: 82px;
}
.product-art::before,
.product-art::after {
  content: "";
  position: absolute;
  border-radius: 8px;
}
.art-headphones::before {
  left: 16px;
  top: 4px;
  width: 60px;
  height: 50px;
  border: 9px solid #202939;
  border-bottom-color: transparent;
  background: transparent;
  border-radius: 42px 42px 12px 12px;
}
.art-headphones::after {
  left: 4px;
  bottom: 4px;
  width: 84px;
  height: 45px;
  background: linear-gradient(135deg, #d0d5dd, #344054);
  clip-path: polygon(11% 20%, 38% 7%, 49% 40%, 83% 8%, 94% 22%, 75% 86%, 52% 78%, 47% 54%, 22% 86%, 5% 70%);
}
.art-watch::before {
  left: 34px;
  top: 0;
  width: 24px;
  height: 82px;
  background: #d0d5dd;
}
.art-watch::after {
  left: 20px;
  top: 16px;
  width: 52px;
  height: 52px;
  border: 5px solid #344054;
  border-radius: 50%;
  background: #667085;
  box-shadow: inset 0 0 0 3px #98a2b3;
}
.art-bag::before {
  left: 17px;
  top: 23px;
  width: 58px;
  height: 52px;
  background: linear-gradient(135deg, #c47f3e, #704722);
}
.art-bag::after {
  left: 29px;
  top: 8px;
  width: 35px;
  height: 31px;
  border: 5px solid #8c5a2b;
  border-bottom: 0;
  background: transparent;
  border-radius: 24px 24px 0 0;
}
.art-shoe::before {
  left: 8px;
  top: 33px;
  width: 78px;
  height: 37px;
  background: linear-gradient(135deg, #98a2b3, #344054);
  clip-path: polygon(8% 44%, 38% 24%, 56% 6%, 78% 19%, 94% 60%, 88% 84%, 18% 87%);
}
.art-shoe::after {
  left: 23px;
  top: 42px;
  width: 44px;
  height: 4px;
  background: #fff;
  transform: rotate(-20deg);
}
.art-camera::before {
  left: 12px;
  top: 22px;
  width: 72px;
  height: 48px;
  background: #253858;
}
.art-camera::after {
  left: 33px;
  top: 31px;
  width: 30px;
  height: 30px;
  border: 6px solid #98a2b3;
  border-radius: 50%;
  background: #111827;
}
.art-speaker::before {
  left: 22px;
  top: 5px;
  width: 48px;
  height: 74px;
  background: linear-gradient(180deg, #344054, #111827);
  border-radius: 18px;
}
.art-speaker::after {
  left: 37px;
  top: 16px;
  width: 18px;
  height: 42px;
  border-top: 12px solid #98a2b3;
  border-bottom: 18px solid #667085;
  border-radius: 999px;
}
.badge {
  position: absolute;
  left: 12px;
  top: 10px;
  height: 20px;
  padding: 0 8px;
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  color: #fff;
  background: #12b76a;
  font-size: 10px;
  font-weight: 900;
}
.badge.sale { background: #f04438; }
.badge.trend { background: #6938ef; }
.product-body {
  flex: 1 1 auto;
  padding: 13px 15px 15px;
  display: flex;
  flex-direction: column;
}
.product-category {
  color: #98a2b3;
  font-size: 11px;
  margin-bottom: 4px;
}
.product-title {
  min-height: 36px;
  color: #344054;
  font-size: 14px;
  line-height: 18px;
  font-weight: 900;
}
.rating {
  display: flex;
  align-items: center;
  gap: 4px;
  color: #f79009;
  font-size: 11px;
  margin: 5px 0 7px;
}
.price-row {
  display: flex;
  align-items: baseline;
  gap: 8px;
  margin-bottom: 10px;
}
.price {
  color: #6938ef;
  font-size: 17px;
  font-weight: 950;
}
.old-price {
  color: #98a2b3;
  font-size: 12px;
  text-decoration: line-through;
}
.card-actions {
  margin-top: auto;
  display: grid;
  grid-template-columns: 1fr 78px;
  gap: 8px;
}
.add-btn,
.buy-btn {
  height: 34px;
  border: 0;
  border-radius: 7px;
  background: #6938ef;
  color: #fff;
  font-size: 12px;
  font-weight: 900;
}
.buy-btn {
  background: #ecfdf3;
  color: #027a48;
}
.pager {
  position: absolute;
  left: 22px;
  right: 22px;
  bottom: 18px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: #667085;
  font-size: 12px;
}
.pager-buttons {
  display: flex;
  align-items: center;
  gap: 7px;
}
.page-btn {
  min-width: 34px;
  height: 34px;
  border: 1px solid #e7ebf3;
  border-radius: 7px;
  background: #fff;
  color: #344054;
  font-weight: 800;
}
.page-btn.active {
  color: #fff;
  background: #6938ef;
  border-color: #6938ef;
}
.store-dim {
  position: absolute;
  inset: 118px 0 0 0;
  z-index: 8;
  background: rgba(15, 23, 42, .08);
  opacity: 0;
  pointer-events: none;
  transition: opacity 280ms ease;
}
.front-stage.drawer-open .store-dim { opacity: 1; }
.cart-launcher {
  position: absolute;
  right: 28px;
  top: 136px;
  z-index: 16;
  width: 58px;
  height: 58px;
  display: grid;
  place-items: center;
  border: 0;
  border-radius: 18px 0 0 18px;
  color: #fff;
  background: #6938ef;
  box-shadow: 0 18px 38px rgba(65, 40, 180, .28);
  transition: transform 260ms ease, background 260ms ease, opacity 220ms ease;
}
.cart-launcher:hover,
.cart-launcher.is-active { background: #2563eb; }
.front-stage.drawer-open .cart-launcher {
  opacity: 0;
  transform: translateX(86px);
  pointer-events: none;
}
.bag-icon {
  width: 24px;
  height: 21px;
  position: relative;
  border: 3px solid #fff;
  border-radius: 5px 5px 7px 7px;
}
.bag-icon::before {
  content: "";
  position: absolute;
  left: 5px;
  top: -11px;
  width: 8px;
  height: 10px;
  border: 3px solid #fff;
  border-bottom: 0;
  border-radius: 999px 999px 0 0;
}
.cart-launcher .count {
  position: absolute;
  right: 8px;
  top: 7px;
  min-width: 18px;
  height: 18px;
  display: grid;
  place-items: center;
  border: 2px solid #6938ef;
  border-radius: 999px;
  background: #fff;
  color: #6938ef;
  font-size: 10px;
  font-weight: 950;
}
.cart-launcher svg {
  width: 28px;
  height: 28px;
}
.drawer {
  position: absolute;
  z-index: 15;
  right: 0;
  top: 118px;
  bottom: 0;
  width: 372px;
  background: #fff;
  border-left: 1px solid #e7ebf3;
  box-shadow: -24px 0 55px rgba(16, 24, 40, .18);
  transform: translateX(104%);
  transition: transform 420ms cubic-bezier(.22, .8, .22, 1);
  display: flex;
  flex-direction: column;
}
.front-stage.drawer-open .drawer { transform: translateX(0); }
.drawer-header {
  flex: 0 0 auto;
  min-height: 72px;
  padding: 20px 18px 13px;
  border-bottom: 1px solid #e7ebf3;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}
.drawer-title {
  margin: 0;
  color: #101828;
  font-size: 21px;
  line-height: 1.1;
}
.drawer-count {
  display: inline-flex;
  align-items: center;
  height: 24px;
  margin-top: 8px;
  padding: 0 10px;
  border-radius: 999px;
  background: #f1e9ff;
  color: #6938ef;
  font-size: 12px;
  font-weight: 950;
}
.icon-button {
  width: 32px;
  height: 32px;
  display: grid;
  place-items: center;
  border: 1px solid #e7ebf3;
  border-radius: 7px;
  background: #fff;
  color: #344054;
  font-size: 16px;
  font-weight: 950;
}
.drawer-scroll {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  scrollbar-width: none;
  padding: 12px 14px 0;
}
.drawer-scroll::-webkit-scrollbar {
  display: none;
}
.selection-bar {
  min-height: 42px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 9px 10px;
  border: 1px solid #e7ebf3;
  border-radius: 8px;
  background: #f8fafc;
  margin-bottom: 10px;
}
.selection-bar label {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #344054;
  font-size: 12px;
  font-weight: 900;
}
.selection-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #667085;
  font-size: 11px;
  font-weight: 800;
}
.remove-selection {
  display: none;
  height: 25px;
  border: 0;
  border-radius: 6px;
  padding: 0 8px;
  color: #fff;
  background: #f04438;
  font-size: 11px;
  font-weight: 900;
}
.front-stage.has-selection .remove-selection { display: inline-flex; align-items: center; }
.group-mode-bar {
  display: flex;
  align-items: center;
  gap: 6px;
  min-height: 34px;
  margin-bottom: 10px;
  padding: 7px 9px;
  border: 1px solid #e7ebf3;
  border-radius: 8px;
  background: #ffffff;
  color: #667085;
  font-size: 10px;
  line-height: 14px;
  font-weight: 900;
}
.group-mode-bar span {
  display: inline-flex;
  align-items: center;
  height: 20px;
  padding: 0 7px;
  border-radius: 999px;
  background: #f2f4f7;
  color: #475467;
  white-space: nowrap;
}
.group-mode-bar span.active {
  color: #6938ef;
  background: #f1e9ff;
}
.cart-items {
  display: grid;
  gap: 10px;
  overflow: visible;
}
.cart-item-group {
  position: relative;
  border: 1px solid #edf1f7;
  border-radius: 9px;
  background: #ffffff;
  overflow: visible;
}
.cart-item-group__title {
  min-height: 34px;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
  padding: 8px 10px;
  color: #24304f;
  background: #f8fafc;
  border-bottom: 1px solid #edf1f7;
  font-size: 12px;
  line-height: 16px;
  font-weight: 950;
}
.location-icon {
  width: 22px;
  height: 22px;
  display: grid;
  place-items: center;
  flex: 0 0 auto;
  border-radius: 7px;
  color: #6938ef;
  background: #f1e9ff;
}
.cart-item-group.is-removed {
  opacity: 0;
  transform: translateX(18px);
  max-height: 0;
  overflow: hidden;
  transition: opacity 260ms ease, transform 260ms ease, max-height 260ms ease;
}
.cart-item-group.is-hidden {
  display: none;
}
.cart-item-group .cart-item {
  margin: 0 10px;
}
.cart-item {
  position: relative;
  display: grid;
  grid-template-columns: 18px 70px minmax(0, 1fr) auto;
  gap: 10px;
  align-items: start;
  padding: 12px 0;
  border-bottom: 1px solid #eef2f6;
  transition: opacity 260ms ease, transform 260ms ease, max-height 260ms ease, padding 260ms ease;
}
.cart-item.is-removed {
  opacity: 0;
  transform: translateX(18px);
  max-height: 0;
  padding-top: 0;
  padding-bottom: 0;
  overflow: hidden;
}
.item-check {
  margin-top: 28px;
}
.item-thumb {
  width: 70px;
  height: 70px;
  border-radius: 8px;
  background: #f2f4f7;
  display: grid;
  place-items: center;
  overflow: hidden;
}
.item-thumb .product-art {
  transform: scale(.72);
}
.item-main { min-width: 0; }
.item-title {
  display: block;
  color: #24304f;
  font-size: 13px;
  line-height: 17px;
  font-weight: 950;
  margin-bottom: 4px;
}
.item-meta {
  color: #667085;
  font-size: 11px;
  line-height: 16px;
  margin-bottom: 8px;
}
.item-actions {
  display: flex;
  align-items: center;
  gap: 7px;
}
.qty {
  display: inline-flex;
  align-items: center;
  border: 1px solid #e7ebf3;
  border-radius: 7px;
  overflow: hidden;
  height: 29px;
}
.qty button {
  width: 28px;
  height: 27px;
  border: 0;
  background: #f8fafc;
  color: #344054;
  font-weight: 950;
}
.qty span {
  min-width: 24px;
  display: grid;
  place-items: center;
  color: #24304f;
  font-size: 12px;
  font-weight: 900;
}
.small-tool {
  height: 29px;
  min-width: 29px;
  display: grid;
  place-items: center;
  border: 1px solid #e7ebf3;
  border-radius: 7px;
  background: #fff;
  color: #6938ef;
  font-weight: 950;
}
.item-price {
  align-self: start;
  color: #6938ef;
  font-size: 13px;
  font-weight: 950;
  white-space: nowrap;
}
.remove-item {
  position: absolute;
  left: 73px;
  top: 15px;
  right: auto;
  bottom: auto;
  width: 25px;
  height: 25px;
  display: grid;
  place-items: center;
  border: 1px solid #fee4e2;
  border-radius: 7px;
  background: #fff5f5;
  color: #f04438;
  font-size: 12px;
  font-weight: 950;
}
.variation-pop {
  display: none;
  position: absolute;
  z-index: 24;
  left: 116px;
  top: 82px;
  width: 214px;
  padding: 12px;
  border: 1px solid #e7ebf3;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 18px 35px rgba(16, 24, 40, .17);
}
.front-stage.variation-open .variation-pop { display: block; }
.front-stage.variation-open [data-main-group] {
  z-index: 30;
}
.variation-pop strong {
  display: block;
  color: #24304f;
  font-size: 12px;
  margin-bottom: 8px;
}
.option-row {
  display: flex;
  gap: 7px;
  margin-bottom: 8px;
}
.option-pill {
  height: 26px;
  min-width: 44px;
  border: 1px solid #e7ebf3;
  border-radius: 7px;
  background: #fff;
  color: #344054;
  font-size: 11px;
  font-weight: 900;
}
.option-pill.active {
  color: #fff;
  background: #6938ef;
  border-color: #6938ef;
}
.variation-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 7px;
}
.variation-actions button {
  height: 28px;
  border: 0;
  border-radius: 7px;
  color: #fff;
  background: #6938ef;
  font-size: 11px;
  font-weight: 950;
}
.variation-actions button:last-child {
  color: #344054;
  background: #eef2f6;
}
.drawer-section {
  margin-top: 12px;
  border: 1px solid #e7ebf3;
  border-radius: 8px;
  background: #fff;
  overflow: hidden;
}
.section-toggle {
  width: 100%;
  height: 42px;
  border: 0;
  background: #f8fafc;
  color: #24304f;
  padding: 0 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 13px;
  font-weight: 950;
}
.section-toggle::after {
  content: "+";
  width: 18px;
  text-align: center;
  font-size: 16px;
}
.drawer-section:not(.is-collapsed) .section-toggle::after { content: "-"; }
.section-body {
  padding: 12px;
  display: grid;
  gap: 9px;
}
.drawer-section.is-collapsed .section-body { display: none; }
.coupon-form {
  display: grid;
  grid-template-columns: 1fr 78px;
  gap: 8px;
}
.coupon-form input {
  min-width: 0;
  height: 36px;
  border: 1px solid #d0d5dd;
  border-radius: 7px;
  padding: 0 11px;
  color: #344054;
  outline: 0;
}
.primary-mini {
  height: 36px;
  border: 0;
  border-radius: 7px;
  background: #6938ef;
  color: #fff;
  font-size: 12px;
  font-weight: 950;
}
.coupon-message,
.applied-coupon {
  display: none;
  min-height: 30px;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  border-radius: 7px;
  padding: 7px 9px;
  font-size: 11px;
  font-weight: 900;
}
.coupon-message {
  color: #027a48;
  background: #ecfdf3;
}
.applied-coupon {
  color: #344054;
  background: #f1e9ff;
}
.front-stage.coupon-applied .coupon-message,
.front-stage.coupon-applied .applied-coupon {
  display: flex;
}
.remove-coupon {
  height: 23px;
  border: 0;
  border-radius: 6px;
  color: #f04438;
  background: #fff;
  font-size: 10px;
  font-weight: 950;
}
.related-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}
.related-card {
  min-width: 0;
  border: 1px solid #eef2f6;
  border-radius: 8px;
  padding: 8px;
  background: #fff;
}
.related-art {
  height: 58px;
  display: grid;
  place-items: center;
  border-radius: 7px;
  background: linear-gradient(135deg, #eff6ff, #e0e7ff);
  margin-bottom: 6px;
  overflow: hidden;
}
.related-art .product-art {
  width: 70px;
  height: 56px;
  transform: none;
}
.related-art .art-camera::before {
  left: 6px;
  top: 10px;
  width: 58px;
  height: 36px;
}
.related-art .art-camera::after {
  left: 24px;
  top: 17px;
  width: 22px;
  height: 22px;
  border-width: 4px;
}
.related-art .art-speaker::before {
  left: 21px;
  top: 4px;
  width: 28px;
  height: 50px;
  border-radius: 12px;
}
.related-art .art-speaker::after {
  left: 30px;
  top: 12px;
  width: 10px;
  height: 28px;
  border-top-width: 8px;
  border-bottom-width: 12px;
}
.related-art .art-watch::before {
  left: 28px;
  top: 0;
  width: 14px;
  height: 56px;
}
.related-art .art-watch::after {
  left: 16px;
  top: 11px;
  width: 38px;
  height: 38px;
  border-width: 4px;
}
.related-card strong {
  display: block;
  color: #24304f;
  font-size: 10px;
  line-height: 13px;
  min-height: 26px;
}
.related-card span {
  display: block;
  color: #6938ef;
  font-size: 11px;
  font-weight: 950;
  margin: 4px 0 6px;
}
.related-card button {
  width: 100%;
  height: 24px;
  border: 0;
  border-radius: 6px;
  background: #eef2ff;
  color: #3843d0;
  font-size: 10px;
  font-weight: 950;
}
.related-card button.is-added {
  color: #027a48;
  background: #dcfae6;
}
.shipping-options label {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 28px;
  color: #344054;
  font-size: 12px;
  font-weight: 800;
}
.shipping-options span {
  margin-left: auto;
  color: #667085;
}
.drawer-footer {
  flex: 0 0 auto;
  padding: 12px 14px 14px;
  border-top: 1px solid #e7ebf3;
  background: linear-gradient(180deg, rgba(255,255,255,.92), #fff 28%);
  box-shadow: 0 -16px 28px rgba(16, 24, 40, .09);
}
.summary-box {
  border: 1px solid #e7ebf3;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 10px;
}
.summary-content {
  display: none;
  padding: 10px 12px 0;
  background: #fff;
}
.front-stage.summary-open .summary-content { display: block; }
.summary-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 27px;
  color: #475467;
  font-size: 12px;
  font-weight: 800;
}
.summary-row.discount { color: #027a48; }
.summary-total {
  width: 100%;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border: 0;
  background: #f8fafc;
  color: #101828;
  padding: 0 12px;
  font-size: 15px;
  font-weight: 950;
}
.summary-total strong { color: #6938ef; font-size: 20px; }
.checkout-btn,
.continue-btn {
  width: 100%;
  height: 44px;
  border: 0;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 950;
}
.checkout-btn {
  color: #fff;
  background: #6938ef;
}
.continue-btn {
  margin-top: 8px;
  color: #344054;
  background: #fff;
  border: 1px solid #d0d5dd;
}
.checkout-panel {
  position: absolute;
  right: 20px;
  bottom: 112px;
  z-index: 7;
  width: 326px;
  padding: 14px;
  border: 1px solid #d0d5dd;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 18px 38px rgba(16, 24, 40, .18);
  opacity: 0;
  transform: translateY(14px);
  pointer-events: none;
  transition: opacity 240ms ease, transform 240ms ease;
}
.front-stage.checkout-open .checkout-panel {
  opacity: 1;
  transform: translateY(0);
}
.checkout-panel strong {
  display: block;
  color: #24304f;
  font-size: 14px;
  margin-bottom: 5px;
}
.checkout-panel span {
  color: #667085;
  font-size: 12px;
  line-height: 17px;
}
.empty-state {
  display: none;
  height: 100%;
  place-items: center;
  text-align: center;
  padding: 34px;
}
.front-stage.empty-cart .drawer-scroll,
.front-stage.empty-cart .drawer-footer { display: none; }
.front-stage.empty-cart .empty-state { display: grid; }
.empty-icon {
  width: 88px;
  height: 88px;
  display: grid;
  place-items: center;
  margin: 0 auto 14px;
  border-radius: 20px;
  background: #f1e9ff;
}
.empty-icon .bag-icon {
  transform: scale(1.55);
  border-color: #6938ef;
}
.empty-icon .bag-icon::before {
  border-color: #6938ef;
}
.empty-state h3 {
  margin: 0 0 8px;
  color: #101828;
  font-size: 21px;
}
.empty-state p {
  max-width: 250px;
  margin: 0 auto 18px;
  color: #667085;
  font-size: 13px;
  line-height: 20px;
}
.topic-rail {
  position: absolute;
  z-index: 14;
  left: 20px;
  top: 150px;
  width: 222px;
  padding: 12px;
  border: 1px solid rgba(208, 213, 221, .82);
  border-radius: 8px;
  background: rgba(255, 255, 255, .95);
  box-shadow: 0 20px 45px rgba(16, 24, 40, .16);
  opacity: 0;
  transform: translateX(-20px);
  transition: opacity 260ms ease, transform 260ms ease;
}
.front-stage.topics-visible .topic-rail {
  opacity: 1;
  transform: translateX(0);
}
.topic-rail h2 {
  margin: 0 0 10px;
  color: #101828;
  font-size: 14px;
  line-height: 18px;
}
.topic-list {
  display: grid;
  gap: 6px;
}
.topic {
  min-height: 35px;
  display: grid;
  grid-template-columns: 22px minmax(0, 1fr);
  align-items: center;
  gap: 8px;
  padding: 7px;
  border: 1px solid #eef2f6;
  border-radius: 7px;
  color: #475467;
  background: #fff;
  font-size: 11px;
  line-height: 14px;
  font-weight: 900;
  opacity: 0;
  transform: translateY(8px);
  transition: opacity 240ms ease, transform 240ms ease, color 160ms ease, border-color 160ms ease, background 160ms ease;
}
.front-stage.topics-visible .topic.is-visible {
  opacity: 1;
  transform: translateY(0);
}
.topic b {
  display: grid;
  place-items: center;
  width: 22px;
  height: 22px;
  border-radius: 7px;
  color: #6938ef;
  background: #f1e9ff;
  font-size: 10px;
}
.topic.is-active {
  color: #101828;
  border-color: #6938ef;
  background: #f7f3ff;
}
.topic.is-active b {
  color: #fff;
  background: #6938ef;
}
.topic.is-done b {
  color: #fff;
  background: #12b76a;
}
.focus-ring {
  position: absolute;
  z-index: 40;
  border: 3px solid rgba(105, 56, 239, .34);
  border-radius: 8px;
  opacity: 0;
  pointer-events: none;
  transition: opacity 180ms ease, left 220ms ease, top 220ms ease, width 220ms ease, height 220ms ease;
}
.focus-ring.is-visible { opacity: 1; }
.cursor {
  position: absolute;
  z-index: 80;
  left: 0;
  top: 0;
  width: 25px;
  height: 25px;
  opacity: 0;
  pointer-events: none;
  transform: translate(-100px, -100px);
  transition: transform 360ms cubic-bezier(.2,.8,.2,1), opacity 180ms ease;
}
.cursor.is-visible { opacity: 1; }
.cursor::before {
  content: "";
  position: absolute;
  left: 3px;
  top: 2px;
  width: 0;
  height: 0;
  border-top: 20px solid #101828;
  border-right: 13px solid transparent;
  filter: drop-shadow(0 4px 5px rgba(16,24,40,.2));
}
.cursor::after {
  content: "";
  position: absolute;
  left: 3px;
  top: 2px;
  width: 0;
  height: 0;
  border-top: 16px solid #fff;
  border-right: 10px solid transparent;
}
.cursor.is-clicking {
  transform: var(--cursor-transform) scale(.88);
}
.click-pulse {
  position: absolute;
  z-index: 79;
  width: 42px;
  height: 42px;
  margin-left: -16px;
  margin-top: -14px;
  border: 3px solid rgba(105, 56, 239, .38);
  border-radius: 50%;
  opacity: 0;
  pointer-events: none;
}
.click-pulse.is-active {
  animation: clickPulse 360ms ease;
}
@keyframes clickPulse {
  0% { opacity: .9; transform: scale(.35); }
  100% { opacity: 0; transform: scale(1.15); }
}
.admin-stage {
  padding: 30px 34px;
  background: #f6f8fb;
}
.admin-shell {
  height: 100%;
  border: 1px solid #dbe2ee;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 26px 70px rgba(16, 24, 40, .12);
  overflow: hidden;
}
.admin-header {
  height: 82px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  border-bottom: 1px solid #e7ebf3;
  background: #fff;
}
.admin-header h2 {
  margin: 0 0 6px;
  color: #101828;
  font-size: 24px;
  line-height: 1.1;
}
.admin-header p {
  margin: 0;
  color: #667085;
  font-size: 13px;
}
.save-btn {
  height: 38px;
  border: 0;
  border-radius: 7px;
  padding: 0 16px;
  color: #fff;
  background: #6938ef;
  font-weight: 950;
}
.admin-main {
  height: calc(100% - 82px);
  display: grid;
  grid-template-columns: 306px 1fr;
  min-height: 0;
}
.admin-controls {
  border-right: 1px solid #e7ebf3;
  background: #f8fafc;
  padding: 16px;
  overflow: hidden;
}
.control-panel {
  display: grid;
  gap: 10px;
}
.control-card {
  min-height: 66px;
  display: grid;
  grid-template-columns: 38px minmax(0, 1fr);
  align-items: center;
  gap: 11px;
  padding: 11px;
  border: 1px solid #e7ebf3;
  border-radius: 8px;
  background: #fff;
  transition: border-color 180ms ease, box-shadow 180ms ease, background 180ms ease;
}
.control-card.is-active {
  border-color: #6938ef;
  background: #f7f3ff;
  box-shadow: 0 0 0 4px rgba(105, 56, 239, .08);
}
.control-icon {
  width: 38px;
  height: 38px;
  display: grid;
  place-items: center;
  border-radius: 8px;
  color: #fff;
  background: #24304f;
  font-weight: 950;
}
.control-card strong {
  display: block;
  color: #24304f;
  font-size: 13px;
  margin-bottom: 4px;
}
.control-card span {
  display: block;
  color: #667085;
  font-size: 11px;
  line-height: 15px;
}
.admin-toggle {
  width: 45px;
  height: 24px;
  border-radius: 999px;
  background: #6938ef;
  position: relative;
}
.admin-toggle::after {
  content: "";
  position: absolute;
  right: 4px;
  top: 4px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #fff;
}
.admin-swatches {
  display: flex;
  gap: 8px;
  margin-top: 7px;
}
.admin-swatch {
  width: 25px;
  height: 25px;
  border: 3px solid #fff;
  border-radius: 999px;
  box-shadow: 0 0 0 1px #cfd7e6;
}
.admin-swatch.is-active {
  box-shadow: 0 0 0 2px #6938ef;
}
.element-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 7px;
  margin-top: 8px;
}
.element-pill {
  height: 24px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  border-radius: 7px;
  color: #344054;
  background: #f8fafc;
  padding: 0 7px;
  font-size: 10px;
  font-weight: 900;
}
.element-pill::before {
  content: "";
  width: 9px;
  height: 9px;
  border-radius: 50%;
  background: #12b76a;
}
.element-pill.is-off {
  color: #98a2b3;
  text-decoration: line-through;
}
.element-pill.is-off::before { background: #d0d5dd; }
.admin-preview {
  position: relative;
  min-width: 0;
  padding: 18px;
  background: #eef2f6;
}
.preview-toolbar {
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 14px;
  border: 1px solid #dbe2ee;
  border-radius: 8px;
  background: #fff;
  margin-bottom: 14px;
}
.preview-toolbar strong {
  color: #24304f;
  font-size: 14px;
}
.preview-tabs {
  display: inline-flex;
  border: 1px solid #e7ebf3;
  border-radius: 7px;
  overflow: hidden;
}
.preview-tabs button {
  height: 30px;
  border: 0;
  padding: 0 12px;
  background: #fff;
  color: #667085;
  font-size: 12px;
  font-weight: 900;
}
.preview-tabs button.active {
  color: #fff;
  background: #6938ef;
}
.preview-canvas {
  position: relative;
  height: calc(100% - 64px);
  border: 1px solid #dbe2ee;
  border-radius: 8px;
  background:
    linear-gradient(90deg, rgba(255,255,255,.58) 1px, transparent 1px),
    linear-gradient(0deg, rgba(255,255,255,.58) 1px, transparent 1px),
    #f8fafc;
  background-size: 28px 28px;
  overflow: hidden;
}
.preview-store {
  position: absolute;
  inset: 18px 178px 18px 18px;
  border-radius: 8px;
  background: #fff;
  border: 1px solid #e7ebf3;
  padding: 14px;
}
.preview-store h3 {
  margin: 0 0 14px;
  color: #24304f;
  font-size: 18px;
}
.preview-lines {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}
.preview-line {
  height: 86px;
  border-radius: 8px;
  background: #f2f4f7;
  position: relative;
  overflow: hidden;
}
.preview-line::before {
  content: "";
  position: absolute;
  left: 12px;
  top: 12px;
  width: 54px;
  height: 54px;
  border-radius: 8px;
  background: #d0d5dd;
}
.preview-line::after {
  content: "";
  position: absolute;
  left: 78px;
  right: 14px;
  top: 18px;
  height: 10px;
  border-radius: 999px;
  background: #d0d5dd;
  box-shadow: 0 20px 0 #e4e7ec, 0 42px 0 #6938ef;
}
.admin-drawer {
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 350px;
  border-left: 1px solid #dbe2ee;
  background: #fff;
  box-shadow: -18px 0 38px rgba(16, 24, 40, .12);
  display: flex;
  flex-direction: column;
  transition: transform 240ms ease;
}
.admin-stage.preview-empty .admin-drawer-filled { display: none; }
.admin-stage:not(.preview-empty) .admin-drawer-empty { display: none; }
.admin-drawer-header {
  padding: 18px 16px 12px;
  border-bottom: 1px solid #e7ebf3;
}
.admin-drawer-header h3 {
  margin: 0 0 8px;
  color: #101828;
  font-size: 20px;
}
.admin-drawer-header span {
  height: 23px;
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 0 9px;
  background: #f1e9ff;
  color: #6938ef;
  font-size: 12px;
  font-weight: 950;
}
.admin-preview-item {
  margin: 12px 14px 0;
  display: grid;
  grid-template-columns: 64px 1fr auto;
  gap: 10px;
  padding-bottom: 12px;
  border-bottom: 1px solid #eef2f6;
}
.admin-preview-image {
  height: 64px;
  border-radius: 8px;
  background: #eef2ff;
}
.admin-preview-copy strong {
  display: block;
  color: #24304f;
  font-size: 13px;
  line-height: 17px;
}
.admin-preview-copy span {
  display: block;
  color: #667085;
  font-size: 11px;
  margin-top: 5px;
}
.admin-preview-price {
  color: #6938ef;
  font-size: 13px;
  font-weight: 950;
}
.admin-coupon-preview,
.admin-summary-preview {
  margin: 12px 14px 0;
  border: 1px solid #e7ebf3;
  border-radius: 8px;
  padding: 12px;
  background: #fff;
}
.admin-coupon-preview strong {
  display: block;
  color: #24304f;
  font-size: 13px;
  margin-bottom: 10px;
}
.admin-input-line {
  height: 34px;
  border-radius: 7px;
  background: #f8fafc;
  border: 1px solid #d0d5dd;
}
.admin-summary-preview {
  margin-top: auto;
  margin-bottom: 12px;
}
.admin-summary-preview div {
  display: flex;
  justify-content: space-between;
  color: #475467;
  font-size: 12px;
  font-weight: 800;
  min-height: 25px;
}
.admin-summary-preview .total {
  color: #101828;
  font-size: 15px;
  border-top: 1px solid #e7ebf3;
  padding-top: 8px;
  margin-top: 6px;
}
.admin-checkout-preview {
  margin: 0 14px 14px;
  height: 42px;
  border: 0;
  border-radius: 8px;
  background: #6938ef;
  color: #fff;
  font-size: 14px;
  font-weight: 950;
}
.admin-drawer-empty {
  flex: 1 1 auto;
  display: grid;
  place-items: center;
  text-align: center;
  padding: 30px;
}
.admin-drawer-empty h3 {
  margin: 14px 0 8px;
  color: #101828;
  font-size: 20px;
}
.admin-drawer-empty p {
  margin: 0 0 16px;
  color: #667085;
  font-size: 13px;
  line-height: 19px;
}
.admin-drawer-empty button {
  height: 38px;
  border: 0;
  border-radius: 7px;
  padding: 0 18px;
  color: #fff;
  background: #6938ef;
  font-weight: 950;
}
.admin-edit-pop {
  position: absolute;
  right: 42px;
  top: 126px;
  z-index: 10;
  width: 260px;
  padding: 14px;
  border: 1px solid #dbe2ee;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 20px 44px rgba(16, 24, 40, .18);
  opacity: 0;
  transform: translateY(12px);
  pointer-events: none;
  transition: opacity 220ms ease, transform 220ms ease;
}
.admin-stage.edit-open .admin-edit-pop {
  opacity: 1;
  transform: translateY(0);
}
.admin-edit-pop strong {
  display: block;
  color: #24304f;
  font-size: 14px;
  margin-bottom: 10px;
}
.admin-edit-pop label {
  display: block;
  color: #667085;
  font-size: 11px;
  font-weight: 900;
  margin-bottom: 6px;
}
.admin-edit-pop input {
  width: 100%;
  height: 34px;
  border: 1px solid #d0d5dd;
  border-radius: 7px;
  padding: 0 10px;
  color: #24304f;
}
.admin-highlight {
  outline: 3px solid rgba(105, 56, 239, .34);
  outline-offset: 3px;
}
.admin-stage {
  padding: 0;
  background: #ffffff;
}
.admin-gif-playback {
  width: 935px;
  height: 988px;
  display: block;
  object-fit: contain;
  background: #ffffff;
}
</style>
</head>
<body>
<div class="showcase" data-mode="frontend">
  <section class="front-stage">
    <header class="store-top">
      <div class="brand"><span class="brand-mark"></span><span>Gadgets Store</span></div>
      <div class="search"><input value="" placeholder="Search products, brands, categories..." readonly><button aria-label="Search"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"></circle><path d="m16.5 16.5 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg></button></div>
      <div class="top-actions">
        <span class="top-action cart-action"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 8h14l-1.4 7.2a2 2 0 0 1-2 1.6H9.1a2 2 0 0 1-2-1.6L5.3 4.8A2 2 0 0 0 3.4 3H2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><circle cx="10" cy="21" r="1.5" fill="currentColor"></circle><circle cx="18" cy="21" r="1.5" fill="currentColor"></circle></svg><span class="top-dot cart-top-count">0</span></span>
        <span>Sarah</span>
      </div>
    </header>
    <nav class="nav-row">
      <span>Home</span><span>Shop</span><span>Categories</span><span>Brands</span><span>Deals</span><span>About</span><span>Contact</span>
    </nav>
    <main class="shop-shell">
      <div class="shop-toolbar">
        <h1>All Products</h1>
        <div class="filter-row">
          <span class="filter-chip primary">All Categories</span>
          <span class="filter-chip">Price Range</span>
          <span class="filter-chip">Brand</span>
          <span class="filter-chip">Rating</span>
          <span class="sort-select">Sort: Best Selling</span>
        </div>
      </div>
      <div class="product-grid">
        <article class="product-card">
          <div class="product-media"><span class="badge sale">-25%</span><span class="product-art art-headphones"></span></div>
          <div class="product-body">
            <div class="product-category">Electronics</div>
            <div class="product-title">Premium Wireless Headphones</div>
            <div class="rating">***** <span>(124 reviews)</span></div>
            <div class="price-row"><span class="price">$149.99</span><span class="old-price">$199.99</span></div>
            <div class="card-actions"><button class="add-btn" data-add-primary>Add to Cart</button><button class="buy-btn">Buy Now</button></div>
          </div>
        </article>
        <article class="product-card">
          <div class="product-media"><span class="badge">New</span><span class="product-art art-watch"></span></div>
          <div class="product-body">
            <div class="product-category">Electronics</div>
            <div class="product-title">Smart Fitness Watch Pro</div>
            <div class="rating">****- <span>(89 reviews)</span></div>
            <div class="price-row"><span class="price">$299.99</span></div>
            <div class="card-actions"><button class="add-btn">Add to Cart</button><button class="buy-btn">Buy Now</button></div>
          </div>
        </article>
        <article class="product-card">
          <div class="product-media"><span class="badge trend">Trending</span><span class="product-art art-bag"></span></div>
          <div class="product-body">
            <div class="product-category">Fashion</div>
            <div class="product-title">Premium Leather Handbag</div>
            <div class="rating">***** <span>(156 reviews)</span></div>
            <div class="price-row"><span class="price">$189.99</span><span class="old-price">$249.99</span></div>
            <div class="card-actions"><button class="add-btn">Add to Cart</button><button class="buy-btn">Buy Now</button></div>
          </div>
        </article>
        <article class="product-card">
          <div class="product-media"><span class="product-art art-shoe"></span></div>
          <div class="product-body">
            <div class="product-category">Sports</div>
            <div class="product-title">Ultra Performance Running Shoes</div>
            <div class="rating">****- <span>(92 reviews)</span></div>
            <div class="price-row"><span class="price">$159.99</span></div>
            <div class="card-actions"><button class="add-btn">Add to Cart</button><button class="buy-btn">Buy Now</button></div>
          </div>
        </article>
        <article class="product-card">
          <div class="product-media"><span class="badge sale">-30%</span><span class="product-art art-camera"></span></div>
          <div class="product-body">
            <div class="product-category">Electronics</div>
            <div class="product-title">Creator Compact Camera Kit</div>
            <div class="rating">***** <span>(201 reviews)</span></div>
            <div class="price-row"><span class="price">$399.99</span><span class="old-price">$549.99</span></div>
            <div class="card-actions"><button class="add-btn">Add to Cart</button><button class="buy-btn">Buy Now</button></div>
          </div>
        </article>
        <article class="product-card">
          <div class="product-media"><span class="badge">New</span><span class="product-art art-speaker"></span></div>
          <div class="product-body">
            <div class="product-category">Audio</div>
            <div class="product-title">Portable Studio Bluetooth Speaker</div>
            <div class="rating">****- <span>(77 reviews)</span></div>
            <div class="price-row"><span class="price">$89.99</span></div>
            <div class="card-actions"><button class="add-btn">Add to Cart</button><button class="buy-btn">Buy Now</button></div>
          </div>
        </article>
      </div>
      <div class="pager">
        <span>Showing 1-24 of 156 products</span>
        <div class="pager-buttons"><button class="page-btn active">1</button><button class="page-btn">2</button><button class="page-btn">3</button><button class="page-btn">4</button><button class="page-btn">Next</button></div>
      </div>
      <div class="topic-rail">
        <h2>Frontend events</h2>
        <div class="topic-list">
          <div class="topic" data-topic="open"><b>1</b><span>Add to cart opens drawer</span></div>
          <div class="topic" data-topic="group"><b>2</b><span>Grouped by category, brand, or meta</span></div>
          <div class="topic" data-topic="select"><b>3</b><span>Select all and remove selected</span></div>
          <div class="topic" data-topic="qty"><b>4</b><span>Quantity controls</span></div>
          <div class="topic" data-topic="variation"><b>5</b><span>Variation switcher</span></div>
          <div class="topic" data-topic="coupon"><b>6</b><span>Coupon apply and remove</span></div>
          <div class="topic" data-topic="shipping"><b>7</b><span>Shipping method change</span></div>
          <div class="topic" data-topic="summary"><b>8</b><span>Collapsible cart summary</span></div>
          <div class="topic" data-topic="related"><b>9</b><span>Related product add</span></div>
          <div class="topic" data-topic="checkout"><b>10</b><span>Checkout and empty cart</span></div>
        </div>
      </div>
    </main>
    <div class="store-dim"></div>
    <button class="cart-launcher" data-cart-launcher aria-label="Open cart"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 8h14l-1.4 7.2a2 2 0 0 1-2 1.6H9.1a2 2 0 0 1-2-1.6L5.3 4.8A2 2 0 0 0 3.4 3H2" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path><circle cx="10" cy="21" r="1.7" fill="currentColor"></circle><circle cx="18" cy="21" r="1.7" fill="currentColor"></circle></svg><span class="count">0</span></button>
    <aside class="drawer">
      <div class="drawer-header">
        <div><h2 class="drawer-title">My Cart</h2><span class="drawer-count">1 item</span></div>
        <button class="icon-button" data-close-drawer aria-label="Close cart"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg></button>
      </div>
      <div class="empty-state">
        <div>
          <div class="empty-icon"><span class="bag-icon"></span></div>
          <h3>Your cart is empty</h3>
          <p>The empty icon, message, and shop button are configurable from the floating cart settings.</p>
          <button class="checkout-btn" data-continue-empty>Shop Now</button>
        </div>
      </div>
      <div class="drawer-scroll">
        <div class="selection-bar">
          <label><input type="checkbox" data-select-all> Select all</label>
          <div class="selection-meta"><span class="selected-count">0 selected</span><button class="remove-selection" data-remove-selected>Remove</button></div>
        </div>
        <div class="group-mode-bar">Group by <span>Category</span><span>Brand</span><span class="active">Cart item meta</span></div>
        <div class="cart-items">
          <div class="cart-item-group" data-main-group>
            <h4 class="cart-item-group__title"><span class="location-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 10c0 5-5.5 10.2-7.4 11.8a1 1 0 0 1-1.2 0C9.5 20.2 4 15 4 10a8 8 0 1 1 16 0Z" stroke="currentColor" stroke-width="2"></path><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"></circle></svg></span>Meta: Warehouse - Dhaka</h4>
            <article class="cart-item" data-main-item>
            <input class="item-check" type="checkbox" data-item-check>
            <div class="item-thumb"><span class="product-art art-headphones"></span></div>
            <div class="item-main">
              <span class="item-title">Premium Wireless Headphones</span>
              <div class="item-meta" data-item-meta>Color: Midnight | Size: M</div>
              <div class="item-actions">
                <div class="qty"><button data-qty-minus>-</button><span data-qty-value>1</span><button data-qty-plus>+</button></div>
                <button class="small-tool" data-variation-button aria-label="Edit variation"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2M10 17h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path><circle cx="16" cy="7" r="2" stroke="currentColor" stroke-width="2"></circle><circle cx="8" cy="17" r="2" stroke="currentColor" stroke-width="2"></circle></svg></button>
              </div>
            </div>
            <strong class="item-price" data-main-price>$149.99</strong>
            <button class="remove-item" data-remove-main aria-label="Remove item"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg></button>
            <div class="variation-pop">
              <strong>Switch variation</strong>
              <div class="option-row"><button class="option-pill">Black</button><button class="option-pill active">Blue</button><button class="option-pill">Tan</button></div>
              <div class="option-row"><button class="option-pill">M</button><button class="option-pill active">L</button></div>
              <div class="variation-actions"><button data-update-variation>Switch</button><button data-cancel-variation>Cancel</button></div>
            </div>
            </article>
          </div>
          <div class="cart-item-group" data-second-group>
            <h4 class="cart-item-group__title"><span class="location-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 10c0 5-5.5 10.2-7.4 11.8a1 1 0 0 1-1.2 0C9.5 20.2 4 15 4 10a8 8 0 1 1 16 0Z" stroke="currentColor" stroke-width="2"></path><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"></circle></svg></span>Meta: Warehouse - Chattogram</h4>
            <article class="cart-item" data-second-item>
            <input class="item-check" type="checkbox">
            <div class="item-thumb"><span class="product-art art-bag"></span></div>
            <div class="item-main">
              <span class="item-title">Premium Leather Handbag</span>
              <div class="item-meta">Material: Leather | Color: Brown</div>
              <div class="item-actions">
                <div class="qty"><button>-</button><span>1</span><button>+</button></div>
              </div>
            </div>
            <strong class="item-price">$189.99</strong>
            <button class="remove-item" aria-label="Remove item"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg></button>
            </article>
          </div>
          <div class="cart-item-group is-hidden" data-related-group>
            <h4 class="cart-item-group__title"><span class="location-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 10c0 5-5.5 10.2-7.4 11.8a1 1 0 0 1-1.2 0C9.5 20.2 4 15 4 10a8 8 0 1 1 16 0Z" stroke="currentColor" stroke-width="2"></path><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"></circle></svg></span>Category: Electronics</h4>
            <article class="cart-item" data-related-item>
            <input class="item-check" type="checkbox">
            <div class="item-thumb"><span class="product-art art-camera"></span></div>
            <div class="item-main">
              <span class="item-title">Compact Camera Kit</span>
              <div class="item-meta">Added from recommendations</div>
              <div class="item-actions">
                <div class="qty"><button>-</button><span>1</span><button>+</button></div>
              </div>
            </div>
            <strong class="item-price">$399.99</strong>
            <button class="remove-item" aria-label="Remove item"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg></button>
            </article>
          </div>
        </div>
        <section class="drawer-section coupon-section">
          <button class="section-toggle" data-coupon-toggle>Have a promo code?</button>
          <div class="section-body">
            <div class="coupon-form"><input data-coupon-input placeholder="Enter code" readonly><button class="primary-mini" data-apply-coupon>Apply</button></div>
            <div class="coupon-message">Coupon SAVE15 applied</div>
            <div class="applied-coupon"><span>SAVE15 discount</span><button class="remove-coupon" data-remove-coupon>Remove</button></div>
          </div>
        </section>
        <section class="drawer-section related-section">
          <button class="section-toggle">You may also like</button>
          <div class="section-body related-grid">
            <div class="related-card"><div class="related-art"><span class="product-art art-camera"></span></div><strong>Compact Camera Kit</strong><span>$399</span><button data-related-add>Add</button></div>
            <div class="related-card"><div class="related-art"><span class="product-art art-speaker"></span></div><strong>Studio Speaker</strong><span>$89</span><button>Add</button></div>
            <div class="related-card"><div class="related-art"><span class="product-art art-watch"></span></div><strong>Fitness Watch</strong><span>$299</span><button>Add</button></div>
          </div>
        </section>
        <section class="drawer-section shipping-section">
          <button class="section-toggle">Shipping options</button>
          <div class="section-body shipping-options">
            <label><input name="ship" type="radio" checked data-ship-standard> Standard <span>Free</span></label>
            <label><input name="ship" type="radio" data-ship-express> Express <span>$9.99</span></label>
            <label><input name="ship" type="radio"> Overnight <span>$19.99</span></label>
          </div>
        </section>
      </div>
      <div class="drawer-footer">
        <div class="summary-box">
          <div class="summary-content">
            <div class="summary-row"><span>Subtotal</span><span data-subtotal>$339.98</span></div>
            <div class="summary-row discount"><span>Discount</span><span data-discount>-$0.00</span></div>
            <div class="summary-row"><span>Shipping</span><span data-shipping>Free</span></div>
            <div class="summary-row"><span>Tax</span><span data-tax>$27.20</span></div>
          </div>
          <button class="summary-total" data-summary-toggle><span>Total</span><strong data-total>$367.18</strong></button>
        </div>
        <button class="checkout-btn" data-checkout>Proceed to Checkout</button>
      </div>
      <div class="checkout-panel"><strong>Popup checkout behavior</strong><span>The drawer can send shoppers to direct checkout or launch the plugin checkout popup from the same button.</span></div>
    </aside>
    <div class="focus-ring"></div>
  </section>
  <section class="admin-stage">
    <img class="admin-gif-playback" data-admin-gif-src="${adminGifUrl}" alt="Floating cart admin visual editor">
  </section>
  <div class="cursor"></div>
  <div class="click-pulse"></div>
</div>
<script>
(() => {
  const root = document.querySelector(".showcase");
  const front = document.querySelector(".front-stage");
  const admin = document.querySelector(".admin-stage");
  const cursor = document.querySelector(".cursor");
  const pulse = document.querySelector(".click-pulse");
  const ring = document.querySelector(".focus-ring");
  const topics = Array.from(document.querySelectorAll(".topic"));
  const state = {
    qty: 1,
    count: 0,
    couponApplied: false,
    express: false,
  };

  function centerOf(selector) {
    const el = document.querySelector(selector);
    if (!el) {
      return { x: 40, y: 40, w: 20, h: 20 };
    }
    const rect = el.getBoundingClientRect();
    return { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2, w: rect.width, h: rect.height, left: rect.left, top: rect.top };
  }

  function pointTo(selector, ringSelector = selector) {
    const point = centerOf(selector);
    cursor.classList.add("is-visible");
    cursor.style.setProperty("--cursor-transform", "translate(" + point.x + "px, " + point.y + "px)");
    cursor.style.transform = "translate(" + point.x + "px, " + point.y + "px)";

    const focus = centerOf(ringSelector);
    ring.classList.add("is-visible");
    ring.style.left = Math.max(0, focus.left - 4) + "px";
    ring.style.top = Math.max(0, focus.top - 4) + "px";
    ring.style.width = Math.max(20, focus.w + 8) + "px";
    ring.style.height = Math.max(20, focus.h + 8) + "px";
  }

  function clickPulse() {
    cursor.classList.add("is-clicking");
    const transform = cursor.style.transform;
    const match = /translate\\(([-.0-9]+)px, ([-.0-9]+)px\\)/.exec(transform);
    if (match) {
      pulse.style.left = match[1] + "px";
      pulse.style.top = match[2] + "px";
    }
    pulse.classList.remove("is-active");
    void pulse.offsetWidth;
    pulse.classList.add("is-active");
    window.setTimeout(() => cursor.classList.remove("is-clicking"), 180);
  }

  function setMode(mode) {
    root.setAttribute("data-mode", mode);
    ring.classList.remove("is-visible");
  }

  function startAdminPlayback() {
    const image = document.querySelector(".admin-gif-playback");
    if (image && !image.getAttribute("src")) {
      image.setAttribute("src", image.getAttribute("data-admin-gif-src"));
    }
    setMode("admin");
    cursor.classList.remove("is-visible");
    ring.classList.remove("is-visible");
  }

  function setCount(count) {
    state.count = count;
    document.querySelector(".cart-launcher .count").textContent = String(count);
    document.querySelector(".cart-top-count").textContent = String(count);
    document.querySelector(".drawer-count").textContent = count === 1 ? "1 item" : count + " items";
  }

  function openDrawer() {
    front.classList.add("drawer-open");
    document.querySelector(".cart-launcher").classList.add("is-active");
    setCount(2);
  }

  function closeDrawer() {
    front.classList.remove("drawer-open");
    document.querySelector(".cart-launcher").classList.remove("is-active");
  }

  function revealTopics() {
    front.classList.add("topics-visible");
    topics.forEach((topic, index) => {
      window.setTimeout(() => topic.classList.add("is-visible"), index * 70);
    });
  }

  function setTopic(name) {
    topics.forEach((topic) => {
      const isCurrent = topic.getAttribute("data-topic") === name;
      if (isCurrent) {
        topic.classList.add("is-visible", "is-active");
        topic.classList.remove("is-done");
      } else {
        topic.classList.remove("is-active");
      }
    });
    const currentIndex = topics.findIndex((topic) => topic.getAttribute("data-topic") === name);
    topics.forEach((topic, index) => {
      if (currentIndex > -1 && index < currentIndex) {
        topic.classList.add("is-done", "is-visible");
      }
    });
  }

  function scrollDrawerTo(selector) {
    const scroller = document.querySelector(".drawer-scroll");
    const target = document.querySelector(selector);
    if (!scroller || !target) {
      return;
    }

    const maxTop = Math.max(0, scroller.scrollHeight - scroller.clientHeight);
    const scrollerRect = scroller.getBoundingClientRect();
    const targetRect = target.getBoundingClientRect();
    const nextTop = Math.max(0, Math.min(scroller.scrollTop + targetRect.top - scrollerRect.top - 8, maxTop));
    scroller.scrollTo({ top: nextTop, behavior: "smooth" });
  }

  function setSelection(selected) {
    document.querySelector("[data-select-all]").checked = selected;
    document.querySelector("[data-item-check]").checked = selected;
    document.querySelectorAll("[data-second-item] .item-check").forEach((input) => {
      input.checked = selected;
    });
    front.classList.toggle("has-selection", selected);
    document.querySelector(".selected-count").textContent = selected ? "2 selected" : "0 selected";
  }

  function removeSelected() {
    setSelection(false);
    document.querySelector("[data-second-group]").classList.add("is-removed");
    setCount(1);
    document.querySelector(".drawer-count").textContent = "1 item";
    const subtotal = state.qty * 149.99;
    updateTotals(subtotal);
  }

  function setQty(qty) {
    state.qty = qty;
    document.querySelector("[data-qty-value]").textContent = String(qty);
    document.querySelector("[data-main-price]").textContent = "$" + (qty * 149.99).toFixed(2);
    updateTotals(qty * 149.99);
  }

  function openVariation(open) {
    front.classList.toggle("variation-open", open);
    if (open) {
      scrollDrawerTo("[data-main-group]");
    }
  }

  function updateVariation() {
    document.querySelector("[data-item-meta]").textContent = "Color: Blue | Size: L";
    openVariation(false);
  }

  function collapseCoupon(collapsed) {
    document.querySelector(".coupon-section").classList.toggle("is-collapsed", collapsed);
  }

  function typeCoupon(value) {
    document.querySelector("[data-coupon-input]").value = value;
  }

  function applyCoupon() {
    state.couponApplied = true;
    front.classList.add("coupon-applied");
    updateTotals(state.qty * 149.99);
  }

  function removeCoupon() {
    state.couponApplied = false;
    front.classList.remove("coupon-applied");
    document.querySelector("[data-coupon-input]").value = "";
    updateTotals(state.qty * 149.99);
  }

  function setShipping(express) {
    state.express = express;
    document.querySelector("[data-ship-express]").checked = express;
    document.querySelector("[data-ship-standard]").checked = !express;
    updateTotals(state.qty * 149.99);
  }

  function toggleSummary(open) {
    front.classList.toggle("summary-open", open);
  }

  function addRelated() {
    const related = document.querySelector("[data-related-group]");
    if (related) {
      related.classList.remove("is-hidden", "is-removed");
    }
    const addButton = document.querySelector("[data-related-add]");
    if (addButton) {
      addButton.textContent = "Added";
      addButton.classList.add("is-added");
    }
    setCount(2);
    updateTotals(state.qty * 149.99 + 399.99);
    scrollDrawerTo("[data-related-group]");
  }

  function openCheckout(open) {
    front.classList.toggle("checkout-open", open);
  }

  function emptyCart() {
    front.classList.add("empty-cart");
    setCount(0);
    document.querySelector(".drawer-count").textContent = "0 items";
  }

  function updateTotals(subtotal) {
    const discount = state.couponApplied ? 15 : 0;
    const shipping = state.express ? 9.99 : 0;
    const tax = Math.max(0, subtotal - discount + shipping) * 0.08;
    const total = subtotal - discount + shipping + tax;
    document.querySelector("[data-subtotal]").textContent = "$" + subtotal.toFixed(2);
    document.querySelector("[data-discount]").textContent = "-$" + discount.toFixed(2);
    document.querySelector("[data-shipping]").textContent = shipping ? "$" + shipping.toFixed(2) : "Free";
    document.querySelector("[data-tax]").textContent = "$" + tax.toFixed(2);
    document.querySelector("[data-total]").textContent = "$" + total.toFixed(2);
  }

  function setAdminControl(name) {
    document.querySelectorAll("[data-admin-control]").forEach((control) => {
      control.classList.toggle("is-active", control.getAttribute("data-admin-control") === name);
    });
  }

  function setAdminSwatch(index) {
    document.querySelectorAll("[data-admin-swatch]").forEach((swatch, swatchIndex) => {
      swatch.classList.toggle("is-active", swatchIndex === index);
    });
    const colors = ["#6938ef", "#0e9384", "#f04438"];
    const color = colors[index] || colors[0];
    document.querySelector(".admin-checkout-preview").style.background = color;
    document.querySelector(".admin-drawer-header span").style.color = color;
    document.querySelector(".admin-drawer-header span").style.background = index === 1 ? "#ccfbef" : index === 2 ? "#fee4e2" : "#f1e9ff";
  }

  function setElementOff(name, off) {
    const pill = document.querySelector('[data-pill="' + name + '"]');
    if (pill) pill.classList.toggle("is-off", off);
    if (name === "coupon") document.querySelector("[data-admin-coupon]").style.display = off ? "none" : "";
    if (name === "summary") document.querySelector("[data-admin-summary]").style.display = off ? "none" : "";
    if (name === "image") document.querySelector(".admin-preview-image").style.display = off ? "none" : "";
  }

  function openAdminEditor(open) {
    admin.classList.toggle("edit-open", open);
  }

  function setAdminTitle(value) {
    document.querySelector("[data-admin-title]").textContent = value;
    document.querySelector("[data-admin-title-input]").value = value;
  }

  function adminPreviewEmpty(empty) {
    admin.classList.toggle("preview-empty", empty);
    document.querySelector("[data-admin-cart-tab]").classList.toggle("active", !empty);
    document.querySelector("[data-admin-empty-tab]").classList.toggle("active", empty);
  }

  window.__floatingShowcase = {
    pointTo,
    clickPulse,
    setMode,
    startAdminPlayback,
    openDrawer,
    closeDrawer,
    revealTopics,
    setTopic,
    scrollDrawerTo,
    setSelection,
    removeSelected,
    setQty,
    openVariation,
    updateVariation,
    collapseCoupon,
    typeCoupon,
    applyCoupon,
    removeCoupon,
    setShipping,
    toggleSummary,
    addRelated,
    openCheckout,
    emptyCart,
    setAdminControl,
    setAdminSwatch,
    setElementOff,
    openAdminEditor,
    setAdminTitle,
    adminPreviewEmpty,
  };

  updateTotals(339.98);
})();
</script>
</body>
</html>`;
}

async function captureFrame(page, frames) {
  const png = await page.screenshot({ fullPage: false, type: "png" });
  frames.push(png);
}

async function hold(page, frames, count, delay = frameDelay) {
  for (let i = 0; i < count; i += 1) {
    await captureFrame(page, frames);
    await page.waitForTimeout(delay);
  }
}

async function runStep(page, frames, count, fn, delay = frameDelay) {
  await page.evaluate(fn);
  await page.waitForTimeout(120);
  await hold(page, frames, count, delay);
}

async function clickStep(page, frames, selector, afterClick, ringSelector = selector, beforeFrames = 3, afterFrames = 5) {
  await page.evaluate(({ selector: target, ringSelector: ringTarget }) => {
    window.__floatingShowcase.pointTo(target, ringTarget);
  }, { selector, ringSelector });
  await hold(page, frames, beforeFrames);
  await page.evaluate(() => window.__floatingShowcase.clickPulse());
  await hold(page, frames, 1, 80);
  if (afterClick) {
    await page.evaluate(afterClick);
  }
  await hold(page, frames, afterFrames);
}

function encodeGif(frames) {
  const gif = GIFEncoder();

  frames.forEach((png, index) => {
    const image = PNG.sync.read(png);
    const palette = quantize(image.data, 256, { format: "rgb565" });
    const indexed = applyPalette(image.data, palette, "rgb565");
    gif.writeFrame(indexed, image.width, image.height, {
      palette,
      delay: index === 0 ? 900 : frameDelay,
      repeat: 0,
    });
  });

  gif.finish();
  return Buffer.from(gif.bytes());
}

async function main() {
  ensureDir(outputDir);
  if (!fs.existsSync(adminGifPath)) {
    throw new Error("Missing admin GIF: " + adminGifPath);
  }

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport, deviceScaleFactor: 1 });
  const adminGifUrl = "data:image/gif;base64," + fs.readFileSync(adminGifPath).toString("base64");
  await page.setContent(buildHtml(adminGifUrl), { waitUntil: "load" });

  const frames = [];

  await hold(page, frames, 8);

  await clickStep(page, frames, "[data-add-primary]", () => {
    window.__floatingShowcase.setTopic("open");
    window.__floatingShowcase.openDrawer();
  }, "[data-add-primary]", 4, 8);

  await runStep(page, frames, 9, () => {
    window.__floatingShowcase.revealTopics();
    window.__floatingShowcase.setTopic("open");
    window.__floatingShowcase.pointTo(".drawer", ".drawer");
  });

  await runStep(page, frames, 7, () => {
    window.__floatingShowcase.setTopic("group");
    window.__floatingShowcase.pointTo(".cart-items", ".cart-items");
  });

  await clickStep(page, frames, "[data-select-all]", () => {
    window.__floatingShowcase.setTopic("select");
    window.__floatingShowcase.setSelection(true);
  }, ".selection-bar", 3, 5);

  await clickStep(page, frames, "[data-remove-selected]", () => {
    window.__floatingShowcase.removeSelected();
  }, ".selection-bar", 3, 6);

  await clickStep(page, frames, "[data-qty-plus]", () => {
    window.__floatingShowcase.setTopic("qty");
    window.__floatingShowcase.setQty(2);
  }, ".qty", 3, 4);

  await clickStep(page, frames, "[data-qty-minus]", () => {
    window.__floatingShowcase.setQty(1);
  }, ".qty", 2, 4);

  await clickStep(page, frames, "[data-variation-button]", () => {
    window.__floatingShowcase.setTopic("variation");
    window.__floatingShowcase.openVariation(true);
  }, "[data-main-item]", 3, 6);

  await clickStep(page, frames, "[data-update-variation]", () => {
    window.__floatingShowcase.updateVariation();
  }, ".variation-pop", 3, 5);

  await clickStep(page, frames, "[data-coupon-toggle]", () => {
    window.__floatingShowcase.setTopic("coupon");
    window.__floatingShowcase.collapseCoupon(true);
  }, ".coupon-section", 3, 4);

  await clickStep(page, frames, "[data-coupon-toggle]", () => {
    window.__floatingShowcase.collapseCoupon(false);
    window.__floatingShowcase.typeCoupon("SAVE15");
  }, ".coupon-section", 2, 5);

  await clickStep(page, frames, "[data-apply-coupon]", () => {
    window.__floatingShowcase.applyCoupon();
  }, ".coupon-section", 3, 6);

  await clickStep(page, frames, "[data-remove-coupon]", () => {
    window.__floatingShowcase.removeCoupon();
  }, ".coupon-section", 3, 5);

  await runStep(page, frames, 3, () => {
    window.__floatingShowcase.typeCoupon("SAVE15");
    window.__floatingShowcase.applyCoupon();
  });

  await runStep(page, frames, 5, () => {
    window.__floatingShowcase.setTopic("shipping");
    window.__floatingShowcase.scrollDrawerTo(".shipping-section");
    window.__floatingShowcase.pointTo(".shipping-section", ".shipping-section");
  });

  await clickStep(page, frames, "[data-ship-express]", () => {
    window.__floatingShowcase.setTopic("shipping");
    window.__floatingShowcase.setShipping(true);
  }, ".shipping-options", 3, 6);

  await clickStep(page, frames, "[data-summary-toggle]", () => {
    window.__floatingShowcase.setTopic("summary");
    window.__floatingShowcase.toggleSummary(true);
  }, ".summary-box", 3, 7);

  await runStep(page, frames, 5, () => {
    window.__floatingShowcase.setTopic("related");
    window.__floatingShowcase.scrollDrawerTo(".related-section");
    window.__floatingShowcase.pointTo(".related-section", ".related-section");
  });

  await clickStep(page, frames, "[data-related-add]", () => {
    window.__floatingShowcase.setTopic("related");
    window.__floatingShowcase.addRelated();
  }, ".related-grid", 3, 6);

  await clickStep(page, frames, "[data-checkout]", () => {
    window.__floatingShowcase.setTopic("checkout");
    window.__floatingShowcase.openCheckout(true);
  }, "[data-checkout]", 3, 7);

  await runStep(page, frames, 4, () => {
    window.__floatingShowcase.scrollDrawerTo("[data-main-group]");
    window.__floatingShowcase.pointTo("[data-remove-main]", "[data-main-item]");
  });

  await clickStep(page, frames, "[data-remove-main]", () => {
    window.__floatingShowcase.setTopic("checkout");
    window.__floatingShowcase.emptyCart();
    window.__floatingShowcase.openCheckout(false);
    window.__floatingShowcase.pointTo("[data-continue-empty]", ".empty-state");
  }, "[data-main-item]", 3, 8);

  await clickStep(page, frames, "[data-continue-empty]", () => {
    window.__floatingShowcase.closeDrawer();
  }, ".empty-state", 3, 5);

  await page.evaluate(() => window.__floatingShowcase.startAdminPlayback());
  await page.waitForFunction(() => {
    const image = document.querySelector(".admin-gif-playback");
    return image && image.complete && image.naturalWidth > 0;
  }, null, { timeout: 20000 });
  await hold(page, frames, 155);

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
