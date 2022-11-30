<?php
class ffmpeg_movie
{




public function __construct($path_to_media, $persistent) {}




public function getDuration() {}




public function getFrameCount() {}




public function getFrameRate() {}




public function getFilename() {}




public function getComment() {}




public function getTitle() {}




public function getAuthor() {}




public function getArtist() {}




public function getCopyright() {}




public function getGenre() {}




public function getTrackNumber() {}




public function getYear() {}




public function getFrameHeight() {}




public function getFrameWidth() {}


public function getPixelFormat() {}




public function getBitRate() {}





public function getVideoBitRate() {}




public function getAudioBitRate() {}




public function getAudioSampleRate() {}




public function getFrameNumber() {}




public function getVideoCodec() {}




public function getAudioCodec() {}




public function getAudioChannels() {}




public function hasAudio() {}




public function hasVideo() {}





public function getFrame($framenumber) {}




public function getNextKeyFrame() {}
}

class ffmpeg_frame
{




public function __construct($gd_image) {}




public function getWidth() {}




public function getHeight() {}




public function getPTS() {}




public function getPresentationTimestamp() {}










public function resize($width, $height, $crop_top = 0, $crop_bottom = 0, $crop_left = 0, $crop_right = 0) {}








public function crop($crop_top, $crop_bottom = 0, $crop_left = 0, $crop_right = 0) {}





public function toGDImage() {}
}

class ffmpeg_animated_gif
{







public function __construct($output_file_path, $width, $height, $frame_rate, $loop_count = 0) {}




public function addFrame(ffmpeg_frame $frame_to_add) {}
}
