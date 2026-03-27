# ProjectRoadmap module migration

Status: Completed

## Scope

- `Modules/ProjectRoadmap/Resources/views/**`

## Migrated in this wave

- Roadmap index actions (status updates, delete, and related quick actions).
- Roadmap show page AJAX tabs / partial reload interactions.
- Task table actions in `ajax/tasks` (quick action updates, timer/status actions, and delete paths).

## API mapping used

- `$.easyAjax` GET -> `window.apiHttp.get`
- `$.easyAjax` POST + serialized/object data -> `window.apiHttp.postUrlEncoded`
- `$.easyAjax` delete patterns -> `window.apiHttp.delete`
- `postUrlEncoded` with `_method=DELETE` retained only where extra payload is needed

## Error handling

- Added `catch(function(err) { $.handleApiFormError(err); })` for migrated requests.

## Remaining easyAjax in module

- None in `Modules/ProjectRoadmap/Resources/views/**`.
