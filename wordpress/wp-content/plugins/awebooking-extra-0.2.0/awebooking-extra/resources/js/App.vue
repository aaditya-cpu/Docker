<template>
	<div class="main extra-price-main">
		<div class="extra-price-row">
			<div>
				<div class="skeleton-input-group">
					<select v-model="selected" :name="prefix + '_extra_price_type'" v-on:change="onChange">
						<option v-for="(value, key) in js_data.chargeType" :value="key">{{ value }}</option>
					</select>
					<p class="cmb2-metabox-description" v-if="selected === ''">{{js_data.text.descTypeNone}}</p>
				</div>
			</div>
		</div>

		<div class="extra-price-row" v-if="selected !== ''">
			<div v-if="selected === 'fixed'">
				<div class="skeleton-input-group">
					<input type="text" :name="prefix + '_extra_fixed_amount'" :value="fixed_value">
					<p class="cmb2-metabox-description">{{js_data.text.descTypeFixed}}</p>
				</div>
			</div>

			<div v-if="selected === 'foreach'">
				<div class="" v-for="(value, index) in foreach_value">
					<span class="skeleton-input-group__addon">{{value.size}}</span>
					<input type="hidden" :name="prefix + '_extra_foreach_amount['+ index +'][size]'" :value="value.size">
					<input type="text" :name="prefix + '_extra_foreach_amount['+ index +'][value]'" :value="value.value">
				</div>
				<p class="cmb2-validate-error" v-if="error">Missing data.</p>
				<p class="cmb2-metabox-description">{{js_data.text.descTypeMandatory}}</p>
			</div>

			<div v-if="selected === 'upto'">
				<div class="" v-for="(value, index) in upto_value">
					<span class="skeleton-input-group__addon">{{value.size}}</span>
					<input type="hidden" :name="prefix + '_extra_upto_amount['+ index +'][size]'" :value="value.size">
					<input type="text" :name="prefix + '_extra_upto_amount['+ index +'][value]'" :value="value.value">
					<button class="extra-price-btn" type="button" @click.prevent="deleteCondition()" v-if="index + 1 === extra_number && index !== 0">&times;</button>
				</div>
				<p>
					<input type="button" :value="js_data.text.add" class="button" @click.prevent="addCondition()">
				</p>
				<p class="cmb2-metabox-description">{{js_data.text.descTypeOptional}}</p>
			</div>
		</div>
	</div>
</template>

<script>
module.exports = {
	props: ['capacity_number', 'prefix', 'type', 'price'],
	data: function() {
		return {
			js_data: awebookingExtraPrice,
			extra_number: 1,
			selected: '',
			fixed_value: 0.0,
			foreach_value: [],
			upto_value: [
				{"size":1,"value":""}
			],
			error: false
		}
	},

	created: function() {
		this.selected = this.type || '';
	    if ( this.selected == 'fixed' ) {
	    	this.fixed_value = this.price;
	    }

	    var capacity_number = this.capacity_number;
	    for (var i=0; i<capacity_number; i++) {
	    	var size = i + 1;
	    	if ( this.foreach_value.length < capacity_number ) {
	    		this.foreach_value.push({"size":size,"value":""});
	    	}
	    }

	    if ( this.selected == 'foreach' ) {
	    	if ( this.price ) {
		    	const foreach_value = JSON.parse(this.price);
		    	const length = foreach_value.length;
		    	if ( length < capacity_number ) {
		    		for (var i=0; i<capacity_number; i++) {
				    	let size = i + 1;
				    	if ( foreach_value.length < size ) {
				    		foreach_value.push({"size":size,"value":""});
				    	}
				    }
		    	}

		    	if ( length > capacity_number ) {
		    		for (var i=0; i<length; i++) {
				    	let size = i + 1;
				    	if ( size > capacity_number ) {
				    		foreach_value.splice(-1, 1);
				    	}
				    }
		    	}

		    	this.foreach_value = foreach_value;
		    	var self = this;
		    	this.foreach_value.forEach(function(element) {
				    if ( ! element.value ) {
				    	self.error = true;
				    }
				});
		    }
	    }

	    if ( this.selected == 'upto' ) {
	    	if ( this.price ) {
		    	const upto_value = JSON.parse(this.price);
		    	const length = upto_value.length;
		    	if ( capacity_number < length ) {
		    		for (var i = 0; i < length; i++) {
		    			let size = i + 1;
		    			if ( size > capacity_number ) {
		    				upto_value.splice(-1, 1);
		    			}
		    		}
		    	}

		    	this.upto_value = upto_value;
		    	this.extra_number = this.upto_value.length;
		    }
	    }
	},

	methods: {
		addCondition() {
			if (this.extra_number >= this.capacity_number) {
				return;
			}
			this.extra_number++;
		    this.upto_value.push({"size":this.extra_number,"value":""});
		},

		deleteCondition() {
			if (this.extra_number === 1) {
				return;
			}

			this.extra_number--;
			this.upto_value.splice(-1, 1);
		},

		onChange() {
			if ( this.type == 'fixed' ) {
		    	this.fixed_value = this.price;
		    }

		    if ( this.type == 'foreach' ) {
		    	if ( this.price ) {
			    	this.foreach_value = JSON.parse(this.price);
			    } else {
			    	var number = this.capacity_number;
				    for (var i=0; i<number; i++) {
				    	var size = i + 1;
				    	if ( this.foreach_value.length < number ) {
				    		this.foreach_value.push({"size":size,"value":""});
				    	}
				    }
			    }
		    }

		    if ( this.type == 'upto' ) {
		    	if ( this.price ) {
			    	this.upto_value = JSON.parse(this.price);
			    	this.extra_number = this.upto_value.length;
			    }
		    }
		}
	}
}
</script>

<style>
	.extra-price-row {
		margin: 0;
    	padding: .5em 1em;
	}

	.extra-price-btn {
	    width: 30px;
	    height: 30px;
	    color: #f44336;
	    font-size: 18px;
	    font-weight: 400;
	    border: none;
	    background-color: #eee;
	    cursor: pointer;
	}

	.extra-price-main {
    	padding: 15px 0;
	}

</style>
