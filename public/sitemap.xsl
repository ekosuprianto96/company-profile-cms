<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template match="/">
        <html>
        <head>
            <title>Sitemap</title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; }
                th { background-color: #f4f4f4; },
                tr td { color: red; }
            </style>
        </head>
        <body>
            <h1>Website Sitemap</h1>
            <table>
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>Last Modified</th>
                    </tr>
                </thead>
                <tbody>
                    <xsl:for-each select="urlset/url">
                        <tr>
                            <td><a href="{loc}"><xsl:value-of select="loc"/></a></td>
                            <td><xsl:value-of select="lastmod"/></td>
                        </tr>
                    </xsl:for-each>
                </tbody>
            </table>
        </body>
        </html>
    </xsl:template>
</xsl:stylesheet>