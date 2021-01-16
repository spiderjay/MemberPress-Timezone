# MemberPressCoupons-Timezone
PHP Snippet: Timezone Fix for MemberPress Coupons

MemberPress coupons for Wordpress are saved by default using the UTC timezone. This code snippet forces MemberPress Coupons to use the correct timezone as defined in Wordpress General Settings.

Coupons with a Start Date or Expiry Eate will run from 00:00:00 on the start date until 11:59:59 on the expiry date in the timezone defined in Wordpress instead of using UTC.

** Snippet should be set to run Only in Administration Area **
