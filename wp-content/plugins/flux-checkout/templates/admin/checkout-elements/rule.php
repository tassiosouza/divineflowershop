<div class="flux-ce-rule-group" v-for="(or_rule, or_idx) in rules">
	<div v-if="or_rule.collapsed" class="flux-ce-rule-collapsed">
		<div class="flux-ce-rule-collapsed__content">
			<ul class="flux-ce-rule-collapsed__content-list">
				<li v-for="summary in getParentRuleSummary( or_rule )" v-html="summary"></li>
			</ul>
		</div>
		<div class="flux-ce-rule-collapsed__action">
			<button class="button button-default flux-ce-rule-collapsed__action-btn" @click="expandRuleGroup(or_rule)"><?php esc_html_e( 'Edit Rule', 'flux-checkout' ); ?></button>
		</div>
	</div>
	<div v-else class="flux-ce-rule-wrap">
		<div class="flux-ce-rules-list">
			<div class="flux-ce-rule flux-ce-rule--open" v-for="rule in or_rule">
				<div class="flux-ce-rule__inner-group flux-ce-rule__inner-group--left">
					<div class="flux-ce-rule__col flux-ce-rule__col--object">
						<select v-model="rule.object" class="flux-ce-input" @change="objectChanged(or_rule)">
							<option value="user_role"><?php esc_html_e( 'User Role', 'flux-checkout' ); ?></option>
							<option value="product"><?php esc_html_e( 'Product', 'flux-checkout' ); ?></option>
							<option value="product_cat"><?php esc_html_e( 'Product Category', 'flux-checkout' ); ?></option>
							<option value="cart_total"><?php esc_html_e( 'Cart total', 'flux-checkout' ); ?></option>
						</select>
					</div>
					<div class="flux-ce-rule__col flux-ce-rule__col--condition">
						<select v-model="rule.condition" class="flux-ce-input flux-ce-input--condition">
							<template v-if='["product", "product_cat"].includes(rule.object)'>
								<option value="is"><?php esc_html_e( 'is in cart', 'flux-checkout' ); ?></option>
								<option value="is_not"><?php esc_html_e( 'is not in cart', 'flux-checkout' ); ?></option>
							</template>
							<template v-if='["user_role"].includes(rule.object)'>
								<option value="is"><?php esc_html_e( 'is', 'flux-checkout' ); ?></option>
								<option value="is_not"><?php esc_html_e( 'is not', 'flux-checkout' ); ?></option>
							</template>
							<option v-if='["cart_total"].includes(rule.object)' value="<"><?php esc_html_e( 'is less than', 'flux-checkout' ); ?></option>
							<option v-if='["cart_total"].includes(rule.object)' value="<="><?php esc_html_e( 'is less than or equal to', 'flux-checkout' ); ?></option>
							<option v-if='["cart_total"].includes(rule.object)' value=">"><?php esc_html_e( 'is more than', 'flux-checkout' ); ?></option>
							<option v-if='["cart_total"].includes(rule.object)' value=">="><?php esc_html_e( 'is more than or equal to', 'flux-checkout' ); ?></option>
						</select>
					</div>

					<div class="flux-ce-rule__col flux-ce-rule__col--value">
						<v-select v-if="'product' === rule.object" 
							placeholder="Search products" 
							:multiple='true' 
							@search="fetchProducts" 
							:options="productOptions" 
							:filterable="false" 
							v-model="rule.value"
							@option:selected="validateOrRule(or_rule)"
							>
							<template slot="no-options">
								<?php esc_html_e( 'Type to search...', 'flux-checkout' ); ?>
							</template>
						</v-select>

						<v-select 
							v-if="'product_cat' === rule.object" 
							placeholder="Search categories" 
							:multiple='true' 
							:options="categoryOptions" 
							v-model="rule.value"
							@option:selected="validateOrRule(or_rule)"
							>
							<template slot="no-options">
								<?php esc_html_e( 'Type to search...', 'flux-checkout' ); ?>
							</template>
						</v-select>

						<input v-if="'cart_total' === rule.object" type="number" v-model="rule.value" @change="validateOrRule(or_rule)" class="flux-ce-input flux-ce-input--value" placeholder="<?php esc_html_e( 'Cart total', 'flux-checkout' ); ?>" v-model="rule.value">
						<select v-if="'user_role' === rule.object" v-model="rule.value" class="flux-ce-input flux-ce-input--value" @change="validateOrRule(or_rule)">
							<?php
							foreach ( get_editable_roles() as $role_id => $_role ) {
								echo sprintf( '<option value="%s">%s</option>', esc_attr( $role_id ), esc_html( $_role['name'] ) );
							}
							?>
							<option value="guest"><?php esc_html_e( 'Guest', 'flux-checkout' ); ?></option>
						</select>

						<div class="flux-ce-rule__error" v-if="rule.error && rule.is_dirty">
							<span  class="flux-ce-rule__error-text">{{ rule.error }}</span>
						</div>
					</div>
					<div class="flux-ce-rule__col flux-ce-rule__col--delete">
						<button class="flux-ce-delete-btn dashicons dashicons-trash" @click="deleteAndRule(rule.id, or_idx)" title="<?php esc_html_e( 'Delete', 'flux-checkout' ); ?>"></button>
						<button class="flux-ce-and-btn dashicons dashicons-plus" @click="addAndRule(or_rule)" title="<?php esc_html_e( 'Add New Condition', 'flux-checkout' ); ?>"></button>
					</div>
				</div>
			</div> <!-- flux-ce-rule -->
		</div> <!-- flux-ce-rules-list -->

		<div class="flux-ce-rule-buttons">
			<button class="button button-default flux-ce-rule-buttons__done" @click="collapseRuleGroup(or_rule)"><?php esc_html_e( 'Done', 'flux-checkout' ); ?></button>
			<a class="flux-ce-rule-buttons__delete button-link-delete" @click="deleteOrRule(or_idx)"><?php esc_html_e( 'Delete', 'flux-checkout' ); ?></a>
		</div>
	</div>

</div> <!-- flux-ce-rule-group -->
