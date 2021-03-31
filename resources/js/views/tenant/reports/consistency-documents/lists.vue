<template>
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="my-0">Consistencia de documentos</h3>
        </div>
        <div class="data-table-visible-columns">
            <el-dropdown :hide-on-click="false">
                <el-button type="primary">
                    Mostrar/Ocultar columnas<i class="el-icon-arrow-down el-icon--right"></i>
                </el-button>
                <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item v-for="(column, index) in columns" :key="index">
                        <el-checkbox v-model="column.visible">{{ column.title }}</el-checkbox>
                    </el-dropdown-item>
                </el-dropdown-menu>
            </el-dropdown>
        </div>
        <div class="card-body">
            <template>
                <el-table :data="tableData" style="width: 100%">
                    <el-table-column label="Serie" prop="serie.number"></el-table-column>
                    <el-table-column label="Número incial" prop="start"></el-table-column>
                    <el-table-column label="Número final" prop="end"></el-table-column>
                    <el-table-column label="Faltantes" prop="diff"></el-table-column>
                    <el-table-column label="Registrados" prop="registered"></el-table-column>
                    <el-table-column v-if="columns.sent.visible" label="Enviados" prop="sent"></el-table-column>
                    <el-table-column v-if="columns.accepted.visible" label="Aceptados" prop="accepted"></el-table-column>
                    <el-table-column v-if="columns.observed.visible" label="Observados" prop="observed"></el-table-column>
                    <el-table-column v-if="columns.rejected.visible" label="Rechazados" prop="rejected"></el-table-column>
                    <el-table-column v-if="columns.canceled.visible" label="Anulados" prop="canceled"></el-table-column>
                    <el-table-column v-if="columns.byVoiding.visible" label="Por anular" prop="byVoiding"></el-table-column>
                </el-table>
            </template>
        </div>
        <tenant-tasks-form :showDialog.sync="showDialog" :tableData.sync="tableData" @refresh="refresh"></tenant-tasks-form>
    </div>
</template>

<script>
    export default {
            data() {
                return {
                resource: 'reports/consistency-documents',
                showDialog: false,
                columns: {
                    sent: {
                        title: 'Enviados',
                        visible: false
                    },
                    accepted: {
                        title: 'Aceptados',
                        visible: false
                    },
                    observed: {
                        title: 'Observados',
                        visible: false
                    },
                    rejected: {
                        title: 'Rechazados',
                        visible: false
                    },
                    canceled: {
                        title: 'Anulados',
                        visible: false
                    },
                    byVoiding: {
                        title: 'Por anular',
                        visible: false
                    },
                },
                tableData: [],
            }
        },
        created() {
            this.refresh();
        },
        methods: {
            refresh() {
                axios.post(`/${this.resource}/lists`).then((response) => {
                    this.tableData = response.data;
                }).catch((error) => {
                    console.log(error);
                }).then(() => {});
            }
        }
    }
</script>
