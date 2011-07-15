<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"; ?>
<a name="2.0"><kml xmlns="http://www.opengis.net/kml/2.2" xmlns:ar="http://www.openarml.org/arml/1.0" xmlns:wikitude="http://www.openarml.org/wikitude/1.0" xmlns:wikitudeInternal="http://www.openarml.org/wikitudeInternal/1.0">

	<Document>
		<ar:provider id="<?php echo $site_id; ?>">
			<ar:name><![CDATA[<?php echo $site_name; ?>]]></ar:name>
			<ar:description><![CDATA[<?php echo $site_tagline; ?>]]></ar:description>
			<wikitude:providerUrl><![CDATA[<?php echo $site_url; ?>]]></wikitude:providerUrl>
			<wikitude:tags/>
			<wikitude:logo><![CDATA[<?php echo $site_logo; ?>]]></wikitude:logo>
			<wikitude:icon><![CDATA[<?php echo $site_logo; ?>]]></wikitude:icon>
		</ar:provider>
		<?php
		foreach ($reports as $report)
		{
			// Get Photos
			$media = ORM::factory("media")
				->where("incident_id", $report->id)
				->where("media_type", 1)
				->find_all();
			
			$thumb = "";
			foreach ($media as $photo)
			{
				if ($photo->media_thumb)
				{ // Get the first thumb
					$prefix = url::base().Kohana::config('upload.relative_directory');
					$thumb = $prefix."/".$photo->media_thumb;
					break;
				}
			}
			?>
			<Placemark id="<?php echo $report->id; ?>">
				<ar:provider><?php echo $site_id; ?></ar:provider>
				<name><![CDATA[<?php echo utf8_encode(htmlspecialchars($report->incident_title)); ?>]]></name>
				<description><![CDATA[ss<?php echo utf8_encode(htmlspecialchars($report->incident_description)); ?>]]></description>
				<wikitude:info>
					<?php
					if ($thumb)
					{
						?><wikitude:thumbnail><![CDATA[<?php echo $thumb; ?>]]></wikitude:thumbnail>
						<?php
					}
					else
					{
						?><wikitude:thumbnail/>
						<?php
					}
					?>
					<wikitude:phone/>
					<wikitude:url><![CDATA[<?php echo $site_url.'reports/view/'.$report->id; ?>]]></wikitude:url>
					<wikitude:email><![CDATA[<?php echo utf8_encode($site_email); ?>]]></wikitude:email>
					<wikitude:address/>
					<?php
					if ( ! $media->count())
					{
						?><wikitude:attachment/>
						<?php
					}
					else
					{
						foreach ($media as $photo)
						{
							?><wikitude:attachment><![CDATA[<?php
							echo url::base().Kohana::config('upload.relative_directory')."/".$photo->media_link;
							?>]]></wikitude:attachment>
							<?php
						}
					}
					?>
				</wikitude:info>
				<Point>
					<coordinates><![CDATA[<?php echo $report->longitude.",".$report->latitude; ?>,]]></coordinates>
				</Point>
			</Placemark>
			<?php 
		}
		?>
	</Document>
</kml>
</a>