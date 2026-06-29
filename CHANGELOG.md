# Changelog

All notable changes to this project will be documented in this file. See [commit-and-tag-version](https://github.com/absolute-version/commit-and-tag-version) for commit guidelines.

## [2.3.1](https://github.com/apivalk/apivalk/compare/v2.3.0...v2.3.1) (2026-06-29)


### Bug Fixes

* improve cache handling for FilesystemCache and RouteCacheFactory ([#148](https://github.com/apivalk/apivalk/issues/148)) ([6004b54](https://github.com/apivalk/apivalk/commit/6004b54ff88d209f5550cea563a88ddfdbb0aa6a)), closes [#147](https://github.com/apivalk/apivalk/issues/147)

## [2.3.0](https://github.com/apivalk/apivalk/compare/v2.2.1...v2.3.0) (2026-06-16)


### Features

* add `SimpleArrayProperty` for scalar array validation and documentation ([#145](https://github.com/apivalk/apivalk/issues/145)) ([94007f6](https://github.com/apivalk/apivalk/commit/94007f6dbee65937d4646621ee6ef67cf435c880)), closes [#144](https://github.com/apivalk/apivalk/issues/144)
* add Makefile, adjust DocBlock paginator handling, and update README formatting ([#143](https://github.com/apivalk/apivalk/issues/143)) ([21b5e8c](https://github.com/apivalk/apivalk/commit/21b5e8ce388f5c0c738f50e4d574c93a5685cfd3)), closes [#142](https://github.com/apivalk/apivalk/issues/142)

## [2.2.1](https://github.com/apivalk/apivalk/compare/v2.2.0...v2.2.1) (2026-05-17)


### Bug Fixes

* improve regex handling and enhance response documentation ([#139](https://github.com/apivalk/apivalk/issues/139)) ([f1fe035](https://github.com/apivalk/apivalk/commit/f1fe035f040489e5f987474c1cbd8ea4b8c60a27))

## [2.2.0](https://github.com/apivalk/apivalk/compare/v2.1.1...v2.2.0) (2026-05-17)


### Features

* add real-world integration suite and framework improvements ([#136](https://github.com/apivalk/apivalk/issues/136)) ([3632bd3](https://github.com/apivalk/apivalk/commit/3632bd38d6aa0e518233a972b647408f0a1d0fff)), closes [#123](https://github.com/apivalk/apivalk/issues/123) [#123](https://github.com/apivalk/apivalk/issues/123)
* enhance request handling with path parameter integration and improved resource creation/update helpers ([#133](https://github.com/apivalk/apivalk/issues/133)) ([5d280da](https://github.com/apivalk/apivalk/commit/5d280da2ea9a697568a4d61f14a8438c34ba4887)), closes [#132](https://github.com/apivalk/apivalk/issues/132)
* introduce `pathProperty()` for typed path parameter definitions ([#129](https://github.com/apivalk/apivalk/issues/129)) ([2b8d516](https://github.com/apivalk/apivalk/commit/2b8d5163e1b558f1b51c4f9ed6bdfa0921bb3a5a)), closes [#128](https://github.com/apivalk/apivalk/issues/128)


### Bug Fixes

* remove unused methods and refactor resource-related controllers and docs ([#131](https://github.com/apivalk/apivalk/issues/131)) ([a821927](https://github.com/apivalk/apivalk/commit/a8219279b72d82d97f04b08ad8e2f89e45443a59)), closes [#127](https://github.com/apivalk/apivalk/issues/127)

## [2.1.1](https://github.com/apivalk/apivalk/compare/v2.1.0...v2.1.1) (2026-05-06)


### Bug Fixes

* add raw value support to filters and improve validation middleware) ([#124](https://github.com/apivalk/apivalk/issues/124)) ([3843931](https://github.com/apivalk/apivalk/commit/384393178c1021bbdea488b80188469b5e3a37a4)), closes [#120](https://github.com/apivalk/apivalk/issues/120)
* add user-requested sort tracking and route default handling in `SortBag` ([#122](https://github.com/apivalk/apivalk/issues/122)) ([b857a7c](https://github.com/apivalk/apivalk/commit/b857a7c6b7f4e63d9a29e6eb12b5ce85d42714a4)), closes [#121](https://github.com/apivalk/apivalk/issues/121)

## [2.1.0](https://github.com/apivalk/apivalk/compare/v2.0.1...v2.1.0) (2026-05-02)


### Features

* boolean filter and unit tests for filters ([#118](https://github.com/apivalk/apivalk/issues/118)) ([3d6a8c3](https://github.com/apivalk/apivalk/commit/3d6a8c31e1526892fb79b721d04d00a40f15ce78)), closes [#103](https://github.com/apivalk/apivalk/issues/103)


### Bug Fixes

* add schema to Content-Language response header ([#114](https://github.com/apivalk/apivalk/issues/114)) ([af8a08c](https://github.com/apivalk/apivalk/commit/af8a08c355fa4f943dbe08924362eaede13d96be)), closes [#111](https://github.com/apivalk/apivalk/issues/111)
* fix SecuritySchemeObject spec compliance and add typed factories ([#115](https://github.com/apivalk/apivalk/issues/115)) ([f0248c2](https://github.com/apivalk/apivalk/commit/f0248c2ad2d80ed0b51b4998b7df572e0e6c39d6)), closes [#112](https://github.com/apivalk/apivalk/issues/112)

## [2.0.1](https://github.com/apivalk/apivalk/compare/v2.0.0...v2.0.1) (2026-04-27)


### Bug Fixes

* skip abstract controllers, tighten paginator type, rename pagination responses ([#110](https://github.com/apivalk/apivalk/issues/110)) ([744ebc0](https://github.com/apivalk/apivalk/commit/744ebc0711bf344afb3900b8ef1c7115c7379d9d)), closes [#104](https://github.com/apivalk/apivalk/issues/104) [#109](https://github.com/apivalk/apivalk/issues/109) [#108](https://github.com/apivalk/apivalk/issues/108)

## [2.0.0](https://github.com/apivalk/apivalk/compare/v1.6.0...v2.0.0) (2026-04-24)


### ⚠ BREAKING CHANGES

* v1 to v2

* add major changes and upgrade guide for v1 to v2 ([#106](https://github.com/apivalk/apivalk/issues/106)) ([9c980ae](https://github.com/apivalk/apivalk/commit/9c980aed1661ae1117da6b96de387aa4247e0246))


### Features

* document locale and rate limit headers in OpenAPI generation ([#96](https://github.com/apivalk/apivalk/issues/96)) ([6650c4e](https://github.com/apivalk/apivalk/commit/6650c4e4c1c0c3140db9fa5c1fd3deeef6aebc68)), closes [#80](https://github.com/apivalk/apivalk/issues/80)
* extend `RouteJsonSerializer` to support orderings in serialization ([#90](https://github.com/apivalk/apivalk/issues/90)) ([f0ee9df](https://github.com/apivalk/apivalk/commit/f0ee9df4683aeb280260724b5455b78b6750c96e)), closes [#86](https://github.com/apivalk/apivalk/issues/86)
* implement comprehensive filtering and sorting systems ([#92](https://github.com/apivalk/apivalk/issues/92)) ([9798249](https://github.com/apivalk/apivalk/commit/97982495b82086d7515420b321d9ecf4200015a8)), closes [#44](https://github.com/apivalk/apivalk/issues/44)
* introduce modular pagination support and enhance schema documentation ([#91](https://github.com/apivalk/apivalk/issues/91)) ([829a2bb](https://github.com/apivalk/apivalk/commit/829a2bb640b9e5d0b026ff27202b837d3a028f6b)), closes [#43](https://github.com/apivalk/apivalk/issues/43)
* **resource:** add resource controllers, responses, and documentation ([#102](https://github.com/apivalk/apivalk/issues/102)) ([f46d085](https://github.com/apivalk/apivalk/commit/f46d08546d0be0c4a37d15b5d15254a14c78eef4)), closes [#78](https://github.com/apivalk/apivalk/issues/78)
* split properties into typed variants with per-type filters, validators, and serialization ([#95](https://github.com/apivalk/apivalk/issues/95)) ([700c289](https://github.com/apivalk/apivalk/commit/700c289b743d0cfa1d24d22a06efeb87bb276bc9)), closes [#94](https://github.com/apivalk/apivalk/issues/94)

## [1.6.0](https://github.com/apivalk/apivalk/compare/v1.5.0...v1.6.0) (2026-04-07)


### Features

* add localization support with `Locale`, `LocalizationConfiguration`, and request-specific locale resolution ([#77](https://github.com/apivalk/apivalk/issues/77)) ([686fa53](https://github.com/apivalk/apivalk/commit/686fa53b163b287cc16d1c029703b00afd5dad0e)), closes [#55](https://github.com/apivalk/apivalk/issues/55)
* enhance `ValidationErrorObject` creation and simplify usage ([#76](https://github.com/apivalk/apivalk/issues/76)) ([1b4b55b](https://github.com/apivalk/apivalk/commit/1b4b55b5c943cb7d919849207f9d8a4d437099e9)), closes [#69](https://github.com/apivalk/apivalk/issues/69)
* include route-specific sorting logic in `AbstractApivalkRequest` ([#84](https://github.com/apivalk/apivalk/issues/84)) ([073709b](https://github.com/apivalk/apivalk/commit/073709bf9e8adc5b7b2f7f28fd692ee839c54673)), closes [#83](https://github.com/apivalk/apivalk/issues/83)
* introduce ordering support for routes ([#82](https://github.com/apivalk/apivalk/issues/82)) ([ff78f74](https://github.com/apivalk/apivalk/commit/ff78f74fb3145a3190bf7bdcde404d80d4e89211)), closes [#45](https://github.com/apivalk/apivalk/issues/45)


### Bug Fixes

* refactor and expand `SecurityMiddlewareTest` for enhanced coverage ([0780ee8](https://github.com/apivalk/apivalk/commit/0780ee86d16107b59ba6fc45703aac262cace97a)), closes [#70](https://github.com/apivalk/apivalk/issues/70)

## [1.5.0](https://github.com/apivalk/apivalk/compare/v1.3.2...v1.5.0) (2026-02-04)

## [1.4.0](https://github.com/apivalk/apivalk/compare/v1.3.1...v1.4.0) (2026-02-01)


### Features

* add composer for dev and fix vulnerability ([325c5d8](https://github.com/apivalk/apivalk/commit/325c5d8cf5428bf6084ca157e316465f4770fe26))
* extend Route with HTTP methods, summary, and fluent API ([5eee0a9](https://github.com/apivalk/apivalk/commit/5eee0a9f653d401b89ad42b33a5e8e3e611714be)), closes [#47](https://github.com/apivalk/apivalk/issues/47)
* refactor security and authorization to improve scope handling ([ee1afd2](https://github.com/apivalk/apivalk/commit/ee1afd2fa8a7244fd46af9d63046605d4a7b3127))
* replace `ErrorObject` with `ValidationErrorObject` for enhanced validation handling ([0fb13e8](https://github.com/apivalk/apivalk/commit/0fb13e8df9dea7b1580e4f097152c317560b5752)), closes [#54](https://github.com/apivalk/apivalk/issues/54)

## [1.4.0](https://github.com/apivalk/apivalk/compare/v1.3.1...v1.4.0) (2026-02-01)


### Features

* add composer for dev and fix vulnerability ([325c5d8](https://github.com/apivalk/apivalk/commit/325c5d8cf5428bf6084ca157e316465f4770fe26))
* extend Route with HTTP methods, summary, and fluent API ([5eee0a9](https://github.com/apivalk/apivalk/commit/5eee0a9f653d401b89ad42b33a5e8e3e611714be)), closes [#47](https://github.com/apivalk/apivalk/issues/47)
* refactor security and authorization to improve scope handling ([ee1afd2](https://github.com/apivalk/apivalk/commit/ee1afd2fa8a7244fd46af9d63046605d4a7b3127))
* replace `ErrorObject` with `ValidationErrorObject` for enhanced validation handling ([0fb13e8](https://github.com/apivalk/apivalk/commit/0fb13e8df9dea7b1580e4f097152c317560b5752)), closes [#54](https://github.com/apivalk/apivalk/issues/54)

## [1.3.1](https://github.com/apivalk/apivalk/compare/v1.3.0...v1.3.1) (2026-01-15)


### Bug Fixes

* datetime validation ([6a251cf](https://github.com/apivalk/apivalk/commit/6a251cfa737bd58ca1036cc0f265e8b4252e6e8b))

## [1.3.0](https://github.com/apivalk/apivalk/compare/v1.2.0...v1.3.0) (2026-01-13)


### Features

* implement authentication and security middleware ([88e23aa](https://github.com/apivalk/apivalk/commit/88e23aac116ddcdc8040f735c13ba74f4f21a767)), closes [#31](https://github.com/apivalk/apivalk/issues/31)
* implement rate limiting ([ed160c4](https://github.com/apivalk/apivalk/commit/ed160c4df48e980ac3214ac353193558e96b7821)), closes [#35](https://github.com/apivalk/apivalk/issues/35)
* improve not found error massage ([f378004](https://github.com/apivalk/apivalk/commit/f37800420e546b0e4d30f8ccc8b9252feb61f9be))

## [1.2.0](https://github.com/apivalk/apivalk/compare/v1.1.1...v1.2.0) (2026-01-04)


### Features

* add cache layer ([5f70627](https://github.com/apivalk/apivalk/commit/5f706273338406dd715f9ce571d6d9bdebc940ba)), closes [#25](https://github.com/apivalk/apivalk/issues/25)
* add PHPStan static analysis ([8d56b30](https://github.com/apivalk/apivalk/commit/8d56b3088f0e75078916e6e47fc1ebb4c476cf60)), closes [#20](https://github.com/apivalk/apivalk/issues/20)
* **logger:** Add logger support ([a0512a2](https://github.com/apivalk/apivalk/commit/a0512a22774cacd89bb2d23e681c7af5835a245f))

## [1.1.1](https://github.com/apivalk/apivalk/compare/v1.1.0...v1.1.1) (2025-12-20)


### Bug Fixes

* **composer:** remove redundant version field from composer.json ([e754f51](https://github.com/apivalk/apivalk/commit/e754f511b220c02fea1a7ea747629273e03e4856))

## 1.1.0 (2025-12-20)


### Features

* initialize Apivalk PHP framework with core components and full test suite ([6be582e](https://github.com/apivalk/apivalk/commit/6be582e7416314e114df4d92042878a132ac704b))
* update ApivalkPHP to apivalk ([79e9f14](https://github.com/apivalk/apivalk/commit/79e9f1440d259557a2cbe57155cf2df9ffa5661b))


### Bug Fixes

* **docblock:** generate shape files in 'Shape' subdirectory and add unit tests ([4ac2579](https://github.com/apivalk/apivalk/commit/4ac2579908ec4b9225c5a9ec30e613029aa38504))

## 1.0.0 (2025-12-20)


### Features

* initialize Apivalk PHP framework with core components and full test suite ([6be582e](https://github.com/apivalk/apivalk/commit/6be582e7416314e114df4d92042878a132ac704b))
* update ApivalkPHP to apivalk ([79e9f14](https://github.com/apivalk/apivalk/commit/79e9f1440d259557a2cbe57155cf2df9ffa5661b))
