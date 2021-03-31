<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">Consulta de Documentos</h3>
        </div>
        <div class="card mb-0">
                <div class="card-body">
                    <data-table :applyCustomer="true" :resource="resource">
                        <tr slot="heading">
                            <th class="">#</th>
                            <th class="">Usuario/Vendedor</th>
                            <th class="">Tipo Documento</th>
                            <th class="">Comprobante</th>
                            <th class="">Fecha emisión</th>
                            <th>Doc. Afectado</th>
                            <th>Cotización</th>
                            <th>Caso</th>
                            <th class="">Cliente</th>
                            <th class="">Estado</th>
                            <th class="">Moneda</th>
                            <th class="">Total Exonerado</th>
                            <th class="">Total Inafecto</th>
                            <th class="">Total Gratuito</th>
                            <th class="">Total Gravado</th>

                            <th class="">Total IGV</th>
                            <th class="">Total</th>
                        <tr>
                        <tr slot-scope="{ index, row }">
                            <td>{{ index }}</td>
                            <td>{{row.user_name}}</td>
                            <td>{{row.document_type_description}}</td>
                            <td>{{row.number}}</td>
                            <td>{{row.date_of_issue}}</td>
                            <td>{{row.affected_document}}</td>
                            <td>{{row.quotation_number_full}}</td>
                            <td>{{row.sale_opportunity_number_full}}</td>
                            <td>{{ row.customer_name }}<br/><small v-text="row.customer_number"></small></td>
                            <td>{{row.state_type_description}}</td>

                            <td>{{ row.currency_type_id}}</td>
                            <!-- <td>{{ (row.document_type_id == '07' || (row.document_type_id!='07' && row.state_type_id =='11')) ? -row.total_exonerated : row.total_exonerated}}</td>

                            <td>{{ (row.document_type_id == '07' || (row.document_type_id!='07' && row.state_type_id =='11')) ? -row.total_unaffected : row.total_unaffected }}</td>
                            <td>{{ (row.document_type_id == '07' || (row.document_type_id!='07' && row.state_type_id =='11')) ? -row.total_free : row.total_free}}</td>
                            <td>{{ (row.document_type_id == '07' || (row.document_type_id!='07' && row.state_type_id =='11')) ? -row.total_taxed : row.total_taxed }}</td>
                            <td>{{ (row.document_type_id == '07' || (row.document_type_id!='07' && row.state_type_id =='11')) ? -row.total_igv : row.total_igv }}</td>
 -->


                            <td>{{ (row.document_type_id == '07') ? ( (row.total_exonerated == 0) ? '0.00': '-'+row.total_exonerated) : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_exonerated) }}</td>

                            <td>{{ (row.document_type_id == '07') ? ( (row.total_unaffected == 0) ? '0.00': '-'+row.total_unaffected) : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_unaffected) }}</td>
                            <td>{{ (row.document_type_id == '07') ? ( (row.total_free == 0) ? '0.00': '-'+row.total_free) : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_free) }}</td>
                            <td>{{ (row.document_type_id == '07') ? ( (row.total_taxed == 0) ? '0.00': '-'+row.total_taxed) : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_taxed) }}</td>
                            <td>{{ (row.document_type_id == '07') ? ( (row.total_igv == 0) ? '0.00': '-'+row.total_igv) : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_igv) }}</td>
                            <td>{{ (row.document_type_id == '07') ? ( (row.total == 0) ? '0.00': '-'+row.total) : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total) }}</td>


                            <!-- <td>{{ (row.document_type_id == '07') ? -row.total_unaffected : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_unaffected) }}</td>
                            <td>{{ (row.document_type_id == '07') ? -row.total_free : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_free) }}</td>
                            <td>{{ (row.document_type_id == '07') ? -row.total_taxed : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_taxed) }}</td>
                            <td>{{ (row.document_type_id == '07') ? -row.total_igv : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_igv) }}</td>
                            <td>{{ (row.document_type_id == '07') ? -row.total : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total) }}</td>  -->

                        </tr>

                    </data-table>


                </div>
        </div>

    </div>
</template>

<script>

    import DataTable from '../../components/DataTableReports.vue'

    export default {
        components: {DataTable},
        data() {
            return {
                resource: 'reports/sales',
                form: {},

            }
        },
        async created() {
        },
        methods: {


        }
    }
</script>
