<?php
namespace IMDC\TerpTubeBundle\Filter;

abstract class FileFilter
{
	const FILTER_NOFILTER = "imdc_terptube.filter.nofilter";
	const FILTER_IMAGE = "imdc_terptube.filter.image";
	const FILTER_VIDEO = "imdc_terptube.filter.video";
	const FILTER_AUDIO = "imdc_terptube.filter.audio";
	const FILTER_RECORD = "imdc_terptube.filter.record";
}
