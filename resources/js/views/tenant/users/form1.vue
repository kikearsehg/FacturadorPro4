<template>
  <el-dialog
    :title="titleDialog"
    :visible="showDialog"
    @close="close"
    @open="create"
  >
    <form autocomplete="off" @submit.prevent="submit">
      <div class="form-body">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group" :class="{ 'has-danger': errors.name }">
              <label class="control-label">Nombre</label>
              <el-input v-model="form.name"></el-input>
              <small
                class="form-control-feedback"
                v-if="errors.name"
                v-text="errors.name[0]"
              ></small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group" :class="{ 'has-danger': errors.email }">
              <label class="control-label">Correo Electrónico</label>
              <el-input
                v-model="form.email"
                :disabled="form.id != null"
              ></el-input>
              <small
                class="form-control-feedback"
                v-if="errors.email"
                v-text="errors.email[0]"
              ></small>
            </div>
          </div>
          <div class="col-md-4">
            <div
              class="form-group"
              :class="{ 'has-danger': errors.establishment_id }"
            >
              <label class="control-label">Establecimiento</label>
              <el-select v-model="form.establishment_id" filterable>
                <el-option
                  v-for="option in establishments"
                  :key="option.id"
                  :value="option.id"
                  :label="option.description"
                ></el-option>
              </el-select>
              <small
                class="form-control-feedback"
                v-if="errors.establishment_id"
                v-text="errors.establishment_id[0]"
              ></small>
            </div>
          </div>
          <div class="col-md-12" v-show="form.id">

            <div class="form-group" :class="{ 'has-danger': errors.api_token }">
              <label class="control-label">Api Token</label>
              <el-input
                v-model="form.api_token"
                :readonly="form.id != null"
              ></el-input>
              <small
                class="form-control-feedback"
                v-if="errors.api_token"
                v-text="errors.api_token[0]"
              ></small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group" :class="{ 'has-danger': errors.password }">
              <label class="control-label">Contraseña</label>
              <el-input v-model="form.password"></el-input>
              <small
                class="form-control-feedback"
                v-if="errors.password"
                v-text="errors.password[0]"
              ></small>
            </div>
          </div>
          <div class="col-md-4">
            <div
              class="form-group"
              :class="{ 'has-danger': errors.password_confirmation }"
            >
              <label class="control-label">Confirmar Contraseña</label>
              <el-input v-model="form.password_confirmation"></el-input>
              <small
                class="form-control-feedback"
                v-if="errors.password_confirmation"
                v-text="errors.password_confirmation[0]"
              ></small>
            </div>
          </div>
          <div class="col-md-4" v-if="typeUser != 'integrator'">
            <div class="form-group" :class="{ 'has-danger': errors.type }">
              <label class="control-label">Perfil</label>
              <el-select v-model="form.type" :disabled="form.id === 1">
                <el-option
                  v-for="option in types"
                  :key="option.type"
                  :value="option.type"
                  :label="option.description"
                ></el-option>
              </el-select>
              <small
                class="form-control-feedback"
                v-if="errors.type"
                v-text="errors.type[0]"
              ></small>
            </div>
          </div>
          <div class="col-md-8" v-if="typeUser != 'integrator'">
            <div class="form-comtrol">
              <label class="control-label">Permisos Módulos</label>

              <div id="app">
                <div class="control_wrapper">
                  <ejs-treeview
                    id="treeview"
                    :fields="fields"
                    :showCheckBox="true"
                    :nodeChecked="nodeChecked"
                  ></ejs-treeview>
                </div>
              </div>
            </div>
          </div>
          <!--
          <div class="col-md-12" v-if="typeUser != 'integrator'">
            <div class="form-group">
              <label class="control-label">Módulos</label>
              <div class="row">
                <div class="col-4" v-for="module in form.modules">
                  <el-checkbox
                    v-model="module.checked"
                    :disabled="form.locked"
                    @change="changeModule(module.id, module.checked)"
                    >{{ module.description }}</el-checkbox
                  >
                </div>
              </div>
            </div>
          </div>
          <div
            class="col-md-12 mt-3"
            v-if="typeUser != 'integrator' && show_levels"
          >
            <div class="form-group">
              <label class="control-label"
                >Nivel de acceso del módulo ventas</label
              >
              <div class="row">
                <div class="col-4" v-for="level in form.levels">
                  <el-checkbox
                    v-model="level.checked"
                    :disabled="form.locked"
                    >{{ level.description }}</el-checkbox
                  >
                </div>
              </div>
            </div>
          </div>-->
        </div>
      </div>
      <div class="form-actions text-right mt-4">
        <el-button @click.prevent="close()">Cancelar</el-button>
        <el-button type="primary" native-type="submit" :loading="loading_submit"
          >Guardar</el-button
        >
      </div>
    </form>
  </el-dialog>
</template>

<script>
import { EventBus } from "../../../helpers/bus";
// import the component
import Treeselect from "@riophae/vue-treeselect";
// import the styles
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

import Vue from "vue";
import { TreeViewPlugin } from "@syncfusion/ej2-vue-navigations";

Vue.use(TreeViewPlugin);

export default {
  props: ["showDialog", "recordId", "typeUser"],
  components: { Treeselect },
  data() {
    return {
      loading_submit: false,
      titleDialog: null,
      resource: "users",
      errors: {},
      form: {},
      modules: [],
      datai: [],
      establishments: [],
      types: [],
      show_levels: false,
      datasource: [],
      fields: {},
      // define the default value
      value: null,
      // define options
      alwaysOpen: true,
      options: [],
    };
  },
  async created() {
    await this.$http.get(`/${this.resource}/tables`).then((response) => {
      //this.options = response.data.datasource;
      this.fields = {
        dataSource: response.data.datasource,
        id: "id",
        parentID: "pid",
        text: "name",
        hasChildren: "hasChild",
      };

      this.modules = response.data.modules;
      this.establishments = response.data.establishments;
      this.types = response.data.types;
    });
    await this.initForm();
  },
  methods: {
    initForm() {
      this.errors = {};
      this.form = {
        id: null,
        name: null,
        email: null,
        api_token: null,
        establishment_id: null,
        password: null,
        password_confirmation: null,
        locked: false,
        type: null,
        modules: [],
        levels: [],
      };
      /*this.modules.forEach((module) => {
        this.form.modules.push({
          id: module.id,
          description: module.description,
          checked: false,
        });
      });*/

      this.show_levels = false;
    },
    create() {
      this.titleDialog = this.recordId ? "Editar Usuario" : "Nuevo Usuario";
      if (this.recordId) {
        this.$http
          .get(`/${this.resource}/record/${this.recordId}`)
          .then((response) => {

            this.fields = {
              dataSource: response.data.data.dataSource,
              id: "id",
              parentID: "pid",
              text: "description",
              hasChildren: "hasChild",
            };
            this.form = response.data.data;
            this.show_levels = this.form.levels.length > 0 ? true : false;
          });
      } else {

        this.$http.get(`/${this.resource}/tables`).then((response) => {
          this.fields = {
            dataSource: response.data.datasource,
            id: "id",
            parentID: "pid",
            text: "name",
            hasChildren: "hasChild",
          };

          this.modules = response.data.modules;
          this.establishments = response.data.establishments;
          this.types = response.data.types;
        });
      }
    },
    submit() {
      this.loading_submit = true;
      this.$http
        .post(`/${this.resource}`, this.form)
        .then((response) => {
          if (response.data.success) {
            this.form.password = null;
            this.form.password_confirmation = null;
            this.$message.success(response.data.message);
            this.$eventHub.$emit("reloadData");
            this.close();
          } else {
            this.$message.error(response.data.message);
          }
        })
        .catch((error) => {
          if (error.response.status === 422) {
            this.errors = error.response.data;
          } else {
            this.$message.error(error.response.data.message);

          }
        })
        .then(() => {
          this.loading_submit = false;
        });
    },
    nodeChecked: function (args) {
      this.form.levels = [];
      this.form.modules = [];
      var treeObj = document.getElementById("treeview").ej2_instances[0];
      var array_d = treeObj.checkedNodes;
      for (let h = 0; h < array_d.length; h++) {
        const element = array_d[h].split("-");
        if (element.length > 1) {
          this.form.modules.push({
            id: element[0],
            checked: true,
          });
          this.form.levels.push({
            id: element[1],
            checked: true,
          });
        } else {
          this.form.modules.push({
            id: array_d[h],
            checked: true,
          });
        }
      }
    },
    close() {

      this.$emit("update:showDialog", false);
      this.initForm();
    },
  },
};
</script>
<style>
@import "../../../../../node_modules/@syncfusion/ej2-base/styles/material.css";
@import "../../../../../node_modules/@syncfusion/ej2-vue-navigations/styles/material.css";
@import "../../../../../node_modules/@syncfusion/ej2-buttons/styles/material.css";
.control_wrapper {
  display: block;
  max-height: 350px;
  overflow: auto;
  border: 1px solid #dddddd;
  border-radius: 3px;
}
.e-checkbox-wrapper .e-frame.e-check, .e-css.e-checkbox-wrapper .e-frame.e-check {
    background-color: #0088CC;
    border-color: transparent;
    color: #fff;
}
.e-checkbox-wrapper:hover .e-frame.e-check, .e-css.e-checkbox-wrapper:hover .e-frame.e-check {
    background-color: #0088CC;
    border-color: transparent;
    color: #fff;
}
</style>
