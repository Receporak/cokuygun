local_build_first:
	make -f docker/local/Makefile build_first

local_rebuild:
	make -f docker/local/Makefile rebuild

local_remove_container:
	make -f docker/local/Makefile remove_container