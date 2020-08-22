/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `portaltemplate`
--
LOCK TABLES `portaltemplate` WRITE;
/*!40000 ALTER TABLE `portaltemplate` DISABLE KEYS */;
INSERT INTO `portaltemplate` VALUES (1,'Jednoduchý portál','div.menu { font-weight: bold; margin-bottom: 10px; overflow: hidden; }\r\ndiv.portalMenuItem { float: left; border: 1px solid black; height: 20px; margin-right: 3px; padding: 5px; }\r\ndiv.portalMenuItem a { text-decoration: none; }\r\ndiv.content { font-style: italic; margin-top: 10px; border: 1px solid black; padding: 5px; }\r\ndiv.registration { border: 1px solid red; width: 100px; height: 100px; margin: 10px; }\r\ndiv.content { width: 700px; height: 450px; overflow: scroll; }\r\ndiv.flb_list div.flb_list_item { margin-bottom: 5px; clear: left; }\r\ndiv.flb_list div.flb_list_item div { float: left; }\r\ndiv.flb_button { margin-left: 10px; font-weight: bold; }\r\ndiv.flb_button:hover { cursor: pointer; }\r\ninput.flb_inputTime { width: 40px; }','<div class=\"menu\"><img src=\"@@FILE(LOGO)\"/>@@MENU()</div>\r\n<div class=\"main\">\r\n   ahojte na novem portalu :)\r\n   <div class=\"content\">@@CONTENT()</div>\r\n</div>',6);
/*!40000 ALTER TABLE `portaltemplate` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Dumping data for table `pagetemplate`
--
LOCK TABLES `pagetemplate` WRITE;
/*!40000 ALTER TABLE `pagetemplate` DISABLE KEYS */;
INSERT INTO `pagetemplate` VALUES (1,'Registrační stránka','<script>\r\nflbInit(\'@@NODE_URL\', @@PROVIDER_ID(), [\r\n{ type: \'flbProfile\', placeHolder: \'f_4\', params: { format: { datetime: \'d.m. H:i\', time: \'H:i\' } }, }\r\n]);\r\n</script>\r\nVítejte na našem skvělém portalu.\r\n\r\nTady se můžete zaregistrovat:\r\n<div id=\"flexbook_4\">\r\n   <div id=\"f_4\">&nbsp;</div>\r\n</div>\r\n\r\nA tady muzete kouknout co nabizime: @@PAGE(<PAGE_ID>,Zdroje)'),(2,'Seznam zdrojů','<script>\r\nflbInit(\'@@NODE_URL\', @@PROVIDER_ID(), [\r\n{ type: \'flbResourceList\', placeHolder: \'f_3\',\r\n   params: { render: [\'reservation\',\'event\',\'occupied\'], format: { datetime: \'d.m. H:i\', time: \'H:i\' } },\r\n}\r\n]);\r\n</script>\r\nA máme pro Vás tyto hřiště:\r\n<div id=\"flexbook_3\">\r\n  <div id=\"f_3\">&nbsp;</div>\r\n</div>'),(3,'Seznam akcí','<script>\r\nflbInit(\'@@NODE_URL\', @@PROVIDER_ID(), [\r\n{ type: \'flbEventList\', placeHolder: \'f_2\',\r\n   params: { format: { datetime: \'d.m. H:i\', time: \'H:i\' } },\r\n}\r\n]);\r\n</script>\r\nV nejbližší době pořádáme tyto akce:\r\n<div id=\"flexbook_2\">\r\n  <div id=\"f_2\">&nbsp;</div>\r\n</div>'),(4,'Další informace','Tady najdete informace, které na ostatních stránkách chybí...');
/*!40000 ALTER TABLE `pagetemplate` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Dumping data for table `portaltemplate_pagetemplate`
--
LOCK TABLES `portaltemplate_pagetemplate` WRITE;
/*!40000 ALTER TABLE `portaltemplate_pagetemplate` DISABLE KEYS */;
INSERT INTO `portaltemplate_pagetemplate` VALUES (1,1,'ITEM_1'),(1,2,'ITEM_3'),(1,3,'ITEM_2'),(1,4,'ITEM_4');
/*!40000 ALTER TABLE `portaltemplate_pagetemplate` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;