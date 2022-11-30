<?php






class FANNConnection
{



public $weight;




public $to_neuron;




public $from_neuron;








public function __construct($from_neuron, $to_neuron, $weight) {}






public function getFromNeuron() {}






public function getToNeuron() {}






public function getWeight() {}








public function setWeight($weight) {}
}













function fann_cascadetrain_on_data($ann, $data, $max_neurons, $neurons_between_reports, $desired_error) {}













function fann_cascadetrain_on_file($ann, $filename, $max_neurons, $neurons_between_reports, $desired_error) {}









function fann_clear_scaling_params($ann) {}









function fann_copy($ann) {}









function fann_create_from_file($configuration_file) {}










function fann_create_shortcut_array($num_layers, $layers) {}











function fann_create_shortcut($num_layers, $num_neurons1, $num_neurons2, ...$_) {}











function fann_create_sparse_array($connection_rate, $num_layers, $layers) {}













function fann_create_sparse($connection_rate, $num_layers, $num_neurons1, $num_neurons2, ...$_) {}










function fann_create_standard_array($num_layers, $layers) {}












function fann_create_standard($num_layers, $num_neurons1, $num_neurons2, ...$_) {}












function fann_create_train_from_callback($num_data, $num_input, $num_output, $user_function) {}











function fann_create_train($num_data, $num_input, $num_output) {}










function fann_descale_input($ann, $input_vector) {}










function fann_descale_output($ann, $output_vector) {}










function fann_descale_train($ann, $train_data) {}









function fann_destroy($ann) {}









function fann_destroy_train($train_data) {}









function fann_duplicate_train_data($data) {}











function fann_get_activation_function($ann, $layer, $neuron) {}











function fann_get_activation_steepness($ann, $layer, $neuron) {}









function fann_get_bias_array($ann) {}









function fann_get_bit_fail_limit($ann) {}









function fann_get_bit_fail($ann) {}









function fann_get_cascade_activation_functions_count($ann) {}









function fann_get_cascade_activation_functions($ann) {}









function fann_get_cascade_activation_steepnesses_count($ann) {}









function fann_get_cascade_activation_steepnesses($ann) {}









function fann_get_cascade_candidate_change_fraction($ann) {}









function fann_get_cascade_candidate_limit($ann) {}









function fann_get_cascade_candidate_stagnation_epochs($ann) {}









function fann_get_cascade_max_cand_epochs($ann) {}









function fann_get_cascade_max_out_epochs($ann) {}









function fann_get_cascade_min_cand_epochs($ann) {}









function fann_get_cascade_min_out_epochs($ann) {}









function fann_get_cascade_num_candidate_groups($ann) {}









function fann_get_cascade_num_candidates($ann) {}









function fann_get_cascade_output_change_fraction($ann) {}









function fann_get_cascade_output_stagnation_epochs($ann) {}









function fann_get_cascade_weight_multiplier($ann) {}









function fann_get_connection_array($ann) {}









function fann_get_connection_rate($ann) {}









function fann_get_errno($errdat) {}









function fann_get_errstr($errdat) {}









function fann_get_layer_array($ann) {}









function fann_get_learning_momentum($ann) {}









function fann_get_learning_rate($ann) {}









function fann_get_MSE($ann) {}









function fann_get_network_type($ann) {}









function fann_get_num_input($ann) {}









function fann_get_num_layers($ann) {}









function fann_get_num_output($ann) {}









function fann_get_quickprop_decay($ann) {}









function fann_get_quickprop_mu($ann) {}









function fann_get_rprop_decrease_factor($ann) {}









function fann_get_rprop_delta_max($ann) {}









function fann_get_rprop_delta_min($ann) {}









function fann_get_rprop_delta_zero($ann) {}









function fann_get_rprop_increase_factor($ann) {}









function fann_get_sarprop_step_error_shift($ann) {}









function fann_get_sarprop_step_error_threshold_factor($ann) {}









function fann_get_sarprop_temperature($ann) {}









function fann_get_sarprop_weight_decay_shift($ann) {}









function fann_get_total_connections($ann) {}









function fann_get_total_neurons($ann) {}









function fann_get_train_error_function($ann) {}









function fann_get_training_algorithm($ann) {}









function fann_get_train_stop_function($ann) {}










function fann_init_weights($ann, $train_data) {}









function fann_length_train_data($data) {}










function fann_merge_train_data($data1, $data2) {}









function fann_num_input_train_data($data) {}









function fann_num_output_train_data($data) {}









function fann_print_error($errdat) {}











function fann_randomize_weights($ann, $min_weight, $max_weight) {}









function fann_read_train_from_file($filename) {}









function fann_reset_errno($errdat) {}









function fann_reset_errstr($errdat) {}









function fann_reset_MSE($ann) {}










function fann_run($ann, $input) {}










function fann_save($ann, $configuration_file) {}










function fann_save_train($data, $file_name) {}










function fann_scale_input($ann, $input_vector) {}











function fann_scale_input_train_data($train_data, $new_min, $new_max) {}










function fann_scale_output($ann, $output_vector) {}











function fann_scale_output_train_data($train_data, $new_min, $new_max) {}











function fann_scale_train_data($train_data, $new_min, $new_max) {}










function fann_scale_train($ann, $train_data) {}










function fann_set_activation_function_hidden($ann, $activation_function) {}











function fann_set_activation_function_layer($ann, $activation_function, $layer) {}










function fann_set_activation_function_output($ann, $activation_function) {}












function fann_set_activation_function($ann, $activation_function, $layer, $neuron) {}










function fann_set_activation_steepness_hidden($ann, $activation_steepness) {}











function fann_set_activation_steepness_layer($ann, $activation_steepness, $layer) {}










function fann_set_activation_steepness_output($ann, $activation_steepness) {}












function fann_set_activation_steepness($ann, $activation_steepness, $layer, $neuron) {}










function fann_set_bit_fail_limit($ann, $bit_fail_limit) {}










function fann_set_callback($ann, $callback) {}










function fann_set_cascade_activation_functions($ann, $cascade_activation_functions) {}










function fann_set_cascade_activation_steepnesses($ann, $cascade_activation_steepnesses_count) {}










function fann_set_cascade_candidate_change_fraction($ann, $cascade_candidate_change_fraction) {}










function fann_set_cascade_candidate_limit($ann, $cascade_candidate_limit) {}










function fann_set_cascade_candidate_stagnation_epochs($ann, $cascade_candidate_stagnation_epochs) {}










function fann_set_cascade_max_cand_epochs($ann, $cascade_max_cand_epochs) {}










function fann_set_cascade_max_out_epochs($ann, $cascade_max_out_epochs) {}










function fann_set_cascade_min_cand_epochs($ann, $cascade_min_cand_epochs) {}










function fann_set_cascade_min_out_epochs($ann, $cascade_min_out_epochs) {}










function fann_set_cascade_num_candidate_groups($ann, $cascade_num_candidate_groups) {}










function fann_set_cascade_output_change_fraction($ann, $cascade_output_change_fraction) {}










function fann_set_cascade_output_stagnation_epochs($ann, $cascade_output_stagnation_epochs) {}










function fann_set_cascade_weight_multiplier($ann, $cascade_weight_multiplier) {}










function fann_set_error_log($errdat, $log_file) {}












function fann_set_input_scaling_params($ann, $train_data, $new_input_min, $new_input_max) {}










function fann_set_learning_momentum($ann, $learning_momentum) {}










function fann_set_learning_rate($ann, $learning_rate) {}












function fann_set_output_scaling_params($ann, $train_data, $new_output_min, $new_output_max) {}










function fann_set_quickprop_decay($ann, $quickprop_decay) {}










function fann_set_quickprop_mu($ann, $quickprop_mu) {}










function fann_set_rprop_decrease_factor($ann, $rprop_decrease_factor) {}










function fann_set_rprop_delta_max($ann, $rprop_delta_max) {}










function fann_set_rprop_delta_min($ann, $rprop_delta_min) {}










function fann_set_rprop_delta_zero($ann, $rprop_delta_zero) {}










function fann_set_rprop_increase_factor($ann, $rprop_increase_factor) {}










function fann_set_sarprop_step_error_shift($ann, $sarprop_step_error_shift) {}










function fann_set_sarprop_step_error_threshold_factor($ann, $sarprop_step_error_threshold_factor) {}










function fann_set_sarprop_temperature($ann, $sarprop_temperature) {}










function fann_set_sarprop_weight_decay_shift($ann, $sarprop_weight_decay_shift) {}














function fann_set_scaling_params($ann, $train_data, $new_input_min, $new_input_max, $new_output_min, $new_output_max) {}










function fann_set_train_error_function($ann, $error_function) {}










function fann_set_training_algorithm($ann, $training_algorithm) {}










function fann_set_train_stop_function($ann, $stop_function) {}










function fann_set_weight_array($ann, $connections) {}












function fann_set_weight($ann, $from_neuron, $to_neuron, $weight) {}









function fann_shuffle_train_data($train_data) {}











function fann_subset_train_data($data, $pos, $length) {}










function fann_test_data($ann, $data) {}











function fann_test($ann, $input, $desired_output) {}










function fann_train_epoch($ann, $data) {}













function fann_train_on_data($ann, $data, $max_epochs, $epochs_between_reports, $desired_error) {}













function fann_train_on_file($ann, $filename, $max_epochs, $epochs_between_reports, $desired_error) {}











function fann_train($ann, $input, $desired_output) {}

define('FANN_TRAIN_INCREMENTAL', 0);
define('FANN_TRAIN_BATCH', 1);
define('FANN_TRAIN_RPROP', 2);
define('FANN_TRAIN_QUICKPROP', 3);
define('FANN_TRAIN_SARPROP', 4);
define('FANN_LINEAR', 0);
define('FANN_THRESHOLD', 1);
define('FANN_THRESHOLD_SYMMETRIC', 2);
define('FANN_SIGMOID', 3);
define('FANN_SIGMOID_STEPWISE', 4);
define('FANN_SIGMOID_SYMMETRIC', 5);
define('FANN_SIGMOID_SYMMETRIC_STEPWISE', 6);
define('FANN_GAUSSIAN', 7);
define('FANN_GAUSSIAN_SYMMETRIC', 8);
define('FANN_GAUSSIAN_STEPWISE', 9);
define('FANN_ELLIOT', 10);
define('FANN_ELLIOT_SYMMETRIC', 11);
define('FANN_LINEAR_PIECE', 12);
define('FANN_LINEAR_PIECE_SYMMETRIC', 13);
define('FANN_SIN_SYMMETRIC', 14);
define('FANN_COS_SYMMETRIC', 15);
define('FANN_SIN', 16);
define('FANN_COS', 17);
define('FANN_ERRORFUNC_LINEAR', 0);
define('FANN_ERRORFUNC_TANH', 1);
define('FANN_STOPFUNC_MSE', 0);
define('FANN_STOPFUNC_BIT', 1);
define('FANN_NETTYPE_LAYER', 0);
define('FANN_NETTYPE_SHORTCUT', 1);
define('FANN_E_NO_ERROR', 0);
define('FANN_E_CANT_OPEN_CONFIG_R', 1);
define('FANN_E_CANT_OPEN_CONFIG_W', 2);
define('FANN_E_WRONG_CONFIG_VERSION', 3);
define('FANN_E_CANT_READ_CONFIG', 4);
define('FANN_E_CANT_READ_NEURON', 5);
define('FANN_E_CANT_READ_CONNECTIONS', 6);
define('FANN_E_WRONG_NUM_CONNECTIONS', 7);
define('FANN_E_CANT_OPEN_TD_W', 8);
define('FANN_E_CANT_OPEN_TD_R', 9);
define('FANN_E_CANT_READ_TD', 10);
define('FANN_E_CANT_ALLOCATE_MEM', 11);
define('FANN_E_CANT_TRAIN_ACTIVATION', 12);
define('FANN_E_CANT_USE_ACTIVATION', 13);
define('FANN_E_TRAIN_DATA_MISMATCH', 14);
define('FANN_E_CANT_USE_TRAIN_ALG', 15);
define('FANN_E_TRAIN_DATA_SUBSET', 16);
define('FANN_E_INDEX_OUT_OF_BOUND', 17);
define('FANN_E_SCALE_NOT_PRESENT', 18);
define('FANN_E_INPUT_NO_MATCH', 19);
define('FANN_E_OUTPUT_NO_MATCH', 20);

define('FANN_VERSION', '2.2');

