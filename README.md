# ea-appointsments-custom-filter
Custom  filter for the Easy Appointments plugin.

Small basic plugin to improve the selectfilter of Easy Appointments.
The plugin adds a new select form and hides the original form. Also a new cancel button is added.
No translation is added.
The code was developped for an application where price is not used.
<ul>
  <li>The selectboxes can be used in any desired order.</li>
  <li>The select options of each selectbox are determined by the selected value of the other two selectboxes.</li>
  <li>The selections can be changed at any time and change triggers a new load of the calendar for the updated select</li>
</ul>
<h2>How to use</h2>
Place the shortcode [eaf-ea-filter] just before the [ea_bootstrap] shortcode.
