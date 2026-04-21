<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
                xmlns:html="http://www.w3.org/TR/REC-html40"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>XML Sitemap - SEO Engine Pro</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style type="text/css">
					body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; color: #3c434a; background: #f0f0f1; margin: 0; padding: 40px; }
					#sitemap { max-width: 900px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
					h1 { font-size: 24px; margin-top: 0; display: flex; align-items: center; gap: 10px; }
					.badge { background: #2271b1; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 11px; text-transform: uppercase; font-weight: 600; }
					p { font-size: 14px; color: #646970; line-height: 1.5; }
					table { width: 100%; border-collapse: collapse; margin-top: 30px; }
					th { text-align: left; padding: 12px; border-bottom: 2px solid #f0f0f1; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #8c8f94; }
					td { padding: 12px; border-bottom: 1px solid #f0f0f1; font-size: 14px; word-break: break-all; }
					tr:hover td { background: #f6f7f7; }
					a { color: #2271b1; text-decoration: none; font-weight: 500; }
					a:hover { text-decoration: underline; }
					.footer { margin-top: 40px; font-size: 11px; text-align: center; color: #a7aaad; }
				</style>
			</head>
			<body>
				<div id="sitemap">
					<h1>XML Sitemap <span class="badge">PRO</span></h1>
					<p>Este mapa do site é gerado automaticamente pelo <strong>SEO Engine Pro</strong> para auxiliar a indexação por buscadores como Google, Bing e IAs generativas.</p>
					
					<xsl:if test="count(//*[local-name()='sitemap']) &gt; 0">
						<table cellpadding="3">
							<thead>
								<tr>
									<th width="75%">Sitemap URL</th>
									<th width="25%">Last Modified</th>
								</tr>
							</thead>
							<tbody>
								<xsl:for_each select="//*[local-name()='sitemap']">
									<tr>
										<td>
											<a href="{*[local-name()='loc']}"><xsl:value-of select="*[local-name()='loc']"/></a>
										</td>
										<td>
											<xsl:value-of select="*[local-name()='lastmod']"/>
										</td>
									</tr>
								</xsl:for_each>
							</tbody>
						</table>
					</xsl:if>

					<xsl:if test="count(//*[local-name()='url']) &gt; 0">
						<table cellpadding="3">
							<thead>
								<tr>
									<th width="75%">URL</th>
									<th width="25%">Last Modified</th>
								</tr>
							</thead>
							<tbody>
								<xsl:for_each select="//*[local-name()='url']">
									<tr>
										<td>
											<a href="{*[local-name()='loc']}"><xsl:value-of select="*[local-name()='loc']"/></a>
										</td>
										<td>
											<xsl:value-of select="*[local-name()='lastmod']"/>
										</td>
									</tr>
								</xsl:for_each>
							</tbody>
						</table>
					</xsl:if>
					
					<div class="footer">
						Gerado por SEO Engine Pro - Otimizado para Performance e IA.
					</div>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
