<?php/*

[TemplateSettings]
ExtensionAutoloadPath[]=ocsearchtools

[RoleSettings]
PolicyOmitList[]=facet/proxy
PolicyOmitList[]=calendar/view
PolicyOmitList[]=calendar/search
PolicyOmitList[]=ocsearch/action
PolicyOmitList[]=repository/server
PolicyOmitList[]=datatable/view
PolicyOmitList[]=classtools/definition
PolicyOmitList[]=classtools/extra_definition

#[Cache]
#CacheItems[]=calendartaxonomy
#CacheItems[]=calendarquery

[Cache_calendartaxonomy]
name=Calendar taxonomy cache
id=calendartaxonomy
tags[]=calendartaxonomy
tags[]=content
path=calendartaxonomy
isClustered=true
class=OCCalendarSearchTaxonomy

[Cache_calendarquery]
name=Calendar query cache
id=calendarquery
tags[]=calendarquery
tags[]=content
path=calendarquery
isClustered=true
class=OCCachedSearchQuery

[Event]
#Listeners[]=classtools/property_is_equal@OCClassToolsFilters::propertyIsEqual
#Listeners[]=classtools/sync_class_attribute@OCClassToolsFilters::filterOriginalAttribute

*/?>
