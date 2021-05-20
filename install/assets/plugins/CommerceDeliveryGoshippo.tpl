//<?php
/**
 * CommerceDeliveryGoshippo
 *
 * CommerceDeliveryGoshippo solution
 *
 * @category    plugin
 * @version     0.10.0
 * @author      mnoskov
 * @internal    @events OnInitializeOrderForm,OnManagerBeforeOrderRender,OnBeforeOrderProcessing,OnCollectSubtotals,OnRegisterDelivery
 * @internal    @properties &title=Delivery method title;text;Goshippo& goshippo_token=Goshippo token;text;& addDeliveryPriceToTotal=Add Delivery Price To Total;list;Yes==1||No==0;1& loadCss=Load css;list;Yes==1||No==0;1& loadJs=Load js;list;Yes==1||No==0;1& showOnlyCountries=Fill country iso which you want see;text;& from_name=Sender first and last name;text;& from_street1=Sender street;text;& from_city=Sender City;text;& from_state=Sender State (only for US & CA);text;& from_zip=Sender Zip;text;& from_country=Sender country (iso-2).Example: 'US' or 'DE'.;text;& tv_weight=TV weight;text;weight& tv_height=TV height;text;height& tv_width=TV width;text;width& tv_length=TV length;text;length& distance_units=The unit used for length, width and height (cm,in,ft,mm,m,yd);text;cm& mass_units=The unit used for weight. (g,oz,lb,kg);text;g& full_name_field=Full name field;text;name
 * @internal    @modx_category Commerce
 * @internal    @disabled 1
 * @internal    @installset base
*/

require MODX_BASE_PATH.'assets/plugins/commerceDeliveryGoshippo/plugin.commerceDeliveryGoshippo.php';