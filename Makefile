TAG?=14-alpine
CONTAINER?=$(shell basename $(CURDIR))-buildchain
DOCKERRUN=docker container run \
	--name ${CONTAINER} \
	--rm \
	-t \
	-v `pwd`:/app \
	${CONTAINER}:${TAG}
DOCSDEST?=../../sites/nystudio107/web/docs/similar

.PHONY: docker docs npm

docker:
	docker build \
		. \
		-t ${CONTAINER}:${TAG} \
		--build-arg TAG=${TAG} \
		--no-cache
docs: docker
	${DOCKERRUN} \
		run docs
	rm -rf ${DOCSDEST}
	mv ./docs/docs/.vuepress/dist ${DOCSDEST}
npm: docker
	${DOCKERRUN} \
		$(filter-out $@,$(MAKECMDGOALS))
%:
	@:
# ref: https://stackoverflow.com/questions/6273608/how-to-pass-argument-to-makefile-from-command-line
