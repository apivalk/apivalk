# Changelog

All notable changes to this project will be documented in this file. See [commit-and-tag-version](https://github.com/absolute-version/commit-and-tag-version) for commit guidelines.

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
