<?php

/*

Отрефакторил только то, что смог однозначно понять по коду метода.

Поскольку метод выдран из контекста (т.е. непонятно с какими 
параметрами он вызывается и как ипользуются возвращаемые им данные)
некоторые вещи отрефакторить невозможно (т.к. они затронут дизайн
класса в котором определен этот метод).

В частности, было-бы неплохо устранить возврат из метода данных разной 
структуры или разобраться в сходстве и различии параметров attachment_metadata
и return_metadata (есть подозрение что их можно одним параметром заменить).


*/

class S3FileUploader extends FileUploader
{
    private $post_id

	private $file_path
	
	private $attachment_metadata
	
	private $return_metadata
	
	public function __construct($post_id, $file_path = null, $attachment_metadata = null)
	{
		$this->post_id = $post_id;
		$this->file_path = $file_path;
		$this->attachment_metadata = $attachment_metadata;
		$this->return_metadata = $attachment_metadata ?? null
	}
	
	public function upload_file($force_new_s3_client = false, $remove_local_files = true) 
	{
		$this->normalize_attachment_metadata();
		if (is_wp_error($this->attachment_metadata)) {
			return $this->attachment_metadata;
		}
	
		// Allow S3 upload to be hijacked / cancelled for any reason
		if (true !== ($error = $this->upload_should_not_be_cancelled())) {
			return $error;
		}
	
		// Check file exists locally before attempting upload
		if (true !== ($error = $this->local_file_exists())) {
			return $error;
		}
		
		// Check mime type of file is in allowed S3 mime types
		if (true !== ($error = $this->is_valid_mime_type()) {
			return $error;
		}
	
		$s3object = $this->get_attachment_s3object();
		$s3client = $this->get_s3client($s3object['region'], $force_new_s3_client);
		
		$args = [
			'Bucket'       => $s3object['bucket'],
			'Key'          => $s3object['key'],
			'SourceFile'   => $s3object['source_file'],
			'ACL'          => $s3object['acl'] ?? self::DEFAULT_ACL,
			'ContentType'  => $s3object['mime_type'],
			'CacheControl' => 'max-age=31536000',
			'Expires'      => date('D, d M Y H:i:s O', time() + 31536000),
		];
		
		if (true !== ($error = $this->putObject($s3client, $args))) {
			return $error;
		}
		
		$this->add_post_meta($s3object);
		
		$this->update_filesize_meta($remove_local_files);
		
		return $this->return_metadata !== null ? $this->attachment_metadata : $s3object;
	}
	
	private function upload_should_not_be_cancelled()
	{
		$pre = apply_filters('as3cf_pre_upload_attachment', false, $this->post_id, $this->attachment_metadata);
		if ($pre !== false) {
			// If the attachment metadata is supplied, return it
			if ($this->return_metadata !== null) {
				return $this->attachment_metadata;
			}
			$error_msg = is_string($pre) ? $pre : __( 'Upload aborted by filter \'as3cf_pre_upload_attachment\'', 'amazon-s3-and-cloudfront');
			return $this->return_upload_error($error_msg);
		}
		return true;
	}
	
	private function is_valid_mime_type()
	{
		$type = get_post_mime_type($this->post_id);
		$allowed_types = $this->get_allowed_mime_types();
		if (in_array($type, $allowed_types)) {
			return true;
		}
		$error_msg = sprintf(__( 'Mime type %s is not allowed', 'amazon-s3-and-cloudfront'), $type);
		return $this->return_upload_error($error_msg, $this->return_metadata);
	}
	
	private function local_file_exists()
	{
		$this->normalizeFilePath()
		if (file_exists($this->file_path)) {
			return true;
		}
		$error_msg = sprintf(__('File %s does not exist', 'amazon-s3-and-cloudfront'), $this->file_path);
		return $this->return_upload_error($error_msg, $this->return_metadata);
	}
	
	private function normalizeFilePath()
	{
		$this->file_path = $this->file_path ?? get_attached_file($post_id, true);
	}
	
	private function normalize_attachment_metadata()
	{
		$this->attachment_metadata = $this->attachment_metadata ?? wp_get_attachment_metadata($this->post_id, true)
	}
	
	private function get_attachment_s3object()
	{
		// check the attachment already exists in S3, eg. edit or restore image
		if ($s3object = $this->get_attachment_s3_info($this->post_id)) {
			// use existing prefix
			$prefix = dirname($s3object['key']);
			$prefix = '.' === $prefix ? '' : $prefix . '/';
			// use existing bucket
			$bucket = $s3object['bucket'];
			// get existing region
			$region = $s3object['region'] ?? '';
		} else {
			// derive prefix from various settings
			$prefix = $this->get_file_prefix($this->get_file_folder_time());
			// use bucket from settings
			$bucket = $this->get_setting('bucket');
			$region = $this->get_setting('region');
			if (is_wp_error($region)) {
				$region = '';
			}
		}
		
		$acl = $this->get_s3object_acl($s3object);
	
		$s3object = [
			'bucket' => $bucket,
			'key'    => $prefix . basename($this->file_path),
			'region' => $region,
		];
		// store acl if not de$s3object['acl']fault
		if ($acl != self::DEFAULT_ACL) {
			$s3object['acl'] = $acl;
		}
		
		return $s3object;
	}
	
	private function get_s3object_acl($s3object)
	{
		$acl = $s3object['acl'] ?? self::DEFAULT_ACL;
		return apply_filters('as3cf_upload_acl', $acl, $this->attachment_metadata, $this->post_id);
	}
	
	private function put_object($s3client, $args)
	{
		$args = apply_filters('as3cf_object_meta', $args, $this->post_id);
		try {
			$s3client->putObject($args);
		} catch (\Exception $e) {
			$error_msg = sprintf(__( 'Error uploading %s to S3: %s', 'amazon-s3-and-cloudfront'), $this->file_path, $e->getMessage());
			return $this->return_upload_error($error_msg, $this->return_metadata);
		}
		return true;
	}
	
	private function add_post_meta($s3object)
	{
		delete_post_meta($this->post_id, 'amazonS3_info');
		add_post_meta($this->post_id, 'amazonS3_info', $s3object);
	}
	
	private function update_filesize_meta($remove_local_files)
	{
		$filesize_total = 0;
		$remove_local_files_setting = $this->get_setting('remove-local-file');
		
		if ($remove_local_files_setting) {
			$files_to_remove = $this->find_all_files_to_remove($this->file_path);	
			$filesize_total = $this->calculate_filesize_total($files_to_remove);
		
			// Store in the attachment meta data for use by WP
			if ($this->return_metadata == null) {
				$bytes = filesize($this->file_path);
				if (false !== $bytes) {
					$this->attachment_metadata['filesize'] = $bytes;
					// Update metadata with filesize
					update_post_meta($this->post_id, '_wp_attachment_metadata', $this->attachment_metadata);
				}
			}
			
			// Store the file size in the attachment meta if we are removing local file
			if ($filesize_total > 0) {
				update_post_meta($this->post_id, 'wpos3_filesize_total', $filesize_total);
			}
			
			if ($remove_local_files) {
				$this->remove_local_files($files_to_remove);
			}
		} else {
			$this->remove_filesize_from_meta()
		}
	}
	
	private function find_all_files_to_remove($file_path)
	{
		$files_to_remove = [$file_path];
		$file_paths = $this->get_attachment_file_paths($this->post_id, true, $this->attachment_metadata);
		foreach ($file_paths as $file_path) {
			if (!in_array($file_path, $files_to_remove)) {
				$files_to_remove[] = $file_path;
			}
		}
		return $files_to_remove;
	}
	
	private function calculate_filesize_total($files_to_remove)
	{
		$filesize_total = 0;
		for ($files_to_remove as $file_path) {
			$bytes = filesize($file_path);
			if (false !== $bytes) {
				$filesize_total += $bytes;
			}
		}
		return filesize_total;
	}
	
	private function remove_filesize_from_meta()
	{
		if (isset($this->attachment_metadata['filesize'])) {
			// Make sure we don't have a cached file sizes in the meta
			unset($this->attachment_metadata['filesize']);
			if ($this->return_metadata == null) {
				// Remove the filesize from the metadata
				update_post_meta($this->post_id, '_wp_attachment_metadata', $this->attachment_metadata);
			}
			delete_post_meta($this->post_id, 'wpos3_filesize_total');
		}
	}
	
	protected function remove_local_files($files_to_remove)
	{
		// Allow other functions to remove files after they have processed
		$files_to_remove = apply_filters('as3cf_upload_attachment_local_files_to_remove', $files_to_remove, $this->post_id, $this->file_path);
		// Remove duplicates
		$files_to_remove = array_unique($files_to_remove);
		// Delete the files
		parent::remove_local_files($files_to_remove);
	}
	
	private function get_file_folder_time()
	{
		if (isset($this->attachment_metadata['file'])) {
			return $this->get_folder_time_from_url($this->attachment_metadata['file']);
		}
		$time = $this->get_attachment_folder_time($this->post_id);
		return date('Y/m', $time);
	}
}