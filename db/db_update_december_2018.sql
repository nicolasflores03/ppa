USE [EAMDEV]
GO

  ALTER TABLE [dbo].[R5_DEADLINE_MAINTENANCE]
  ADD 
	Q1 tinyint DEFAULT 0 ,
	Q2 tinyint DEFAULT 0 ,
	Q3 tinyint DEFAULT 0 ,
	Q4 tinyint DEFAULT 0 ;

	ALTER TABLE [dbo].[R5_BUDGET_MOVEMENT]
	ADD 
	fr_quarter int null ,
	to_quarter int null ,
	to_org_code int null
	;
	

  ALTER VIEW [dbo].[R5_VIEW_BUDGET_MOVEMENT]
AS
SELECT     id, app_id, replace(convert(NVARCHAR, dbo.R5_BUDGET_MOVEMENT.createdAt, 101), ' ', '/') as created_date,
					dbo.R5_BUDGET_MOVEMENT.ORG_CODE, to_table, fr_table,
                          (SELECT     TOP (1) MRC_DESC
                            FROM          dbo.R5MRCS
                            WHERE      (MRC_CODE COLLATE Latin1_General_CI_AS = dbo.R5_BUDGET_MOVEMENT.FR_MRC_CODE)) AS Source_Department, FR_MRC_CODE,
                          (SELECT     TOP (1) MRC_DESC
                            FROM          dbo.R5MRCS AS R5MRCS_1
                            WHERE      (MRC_CODE COLLATE Latin1_General_CI_AS = dbo.R5_BUDGET_MOVEMENT.TO_MRC_CODE)) AS Destination_Department, TO_MRC_CODE, 
                      CASE fr_table WHEN 'IB' THEN
                          (SELECT     TOP (1) Description COLLATE Latin1_General_CI_AS
                            FROM          dbo.R5_VIEW_ITEMBASE_LINES
                            WHERE      (id = dbo.R5_BUDGET_MOVEMENT.fr_code)) ELSE
                          (SELECT     TOP (1) description
                            FROM          dbo.R5_VIEW_COSTBASE_LINES
                            WHERE      (id = dbo.R5_BUDGET_MOVEMENT.fr_code)) END AS Source, fr_code, CASE to_table WHEN 'IB' THEN
                          ((SELECT     TOP (1) Description COLLATE Latin1_General_CI_AS
                              FROM         dbo.R5_VIEW_ITEMBASE_LINES AS R5_VIEW_ITEMBASE_LINES_1
                              WHERE     (id = dbo.R5_BUDGET_MOVEMENT.to_code))) ELSE
                          ((SELECT     TOP (1) description
                              FROM         dbo.R5_VIEW_COSTBASE_LINES AS R5_VIEW_ITEMBASE_LINES_1
                              WHERE     (id = dbo.R5_BUDGET_MOVEMENT.to_code))) END AS Destination, to_code, amount, to_available_amount, fr_available_amount, type, year_budget,  
							  
							  (CASE
									WHEN
									dbo.R5_BUDGET_MOVEMENT.to_quarter = 1
										THEN
										'Q1'
									WHEN
									dbo.R5_BUDGET_MOVEMENT.to_quarter = 2
										THEN
										'Q2'
									WHEN
									dbo.R5_BUDGET_MOVEMENT.to_quarter = 3
										THEN
										'Q3'
									WHEN
										dbo.R5_BUDGET_MOVEMENT.to_quarter = 4
										THEN
											'Q4'
									ELSE  
										''
								End ) as destination_quarter,  dbo.R5_BUDGET_MOVEMENT.to_quarter,

								 (CASE
									WHEN
									dbo.R5_BUDGET_MOVEMENT.fr_quarter = 1
										THEN
										'Q1'
									WHEN
									dbo.R5_BUDGET_MOVEMENT.fr_quarter = 2
										THEN
										'Q2'
									WHEN
									dbo.R5_BUDGET_MOVEMENT.fr_quarter = 3
										THEN
										'Q3'
									WHEN
										dbo.R5_BUDGET_MOVEMENT.fr_quarter = 4
										THEN
											'Q4'
									ELSE  
										''
								End ) as source_quarter,  dbo.R5_BUDGET_MOVEMENT.fr_quarter,  dbo.R5_BUDGET_MOVEMENT.to_org_code, org_rec.ORG_DESC COLLATE Latin1_General_CI_AS as destination_organization ,
                      fr_cost_center, cost_center, status, reason, updatedAt, remarks
					FROM  dbo.R5_BUDGET_MOVEMENT
					LEFT OUTER JOIN dbo.R5ORGANIZATION AS org_rec ON dbo.R5_BUDGET_MOVEMENT.ORG_CODE = org_rec.ORG_CODE COLLATE Latin1_General_CI_AS AND org_rec.ORG_CODE  != '*' 
				





/****** Object:  Table [dbo].[R5_REF_ITEMBASE_BUDGET_QUARTERLY]  ******/


CREATE TABLE [dbo].[R5_REF_ITEMBASE_BUDGET_QUARTERLY](
	[id] [int] NOT NULL,
	[q1_total_cost] [numeric](24, 6) NULL,
	[q1_adjustments] [numeric](24, 6) NULL,
	[q1_available] [numeric](24, 6) NULL,
	[q1_reserved] [numeric](24, 6) NULL,
	[q1_allocated] [numeric](24, 6) NULL,
	[q1_paid] [numeric](24, 6) NULL,

	[q2_total_cost] [numeric](24, 6) NULL,
	[q2_adjustments] [numeric](24, 6) NULL,
	[q2_available] [numeric](24, 6) NULL,
	[q2_reserved] [numeric](24, 6) NULL,
	[q2_allocated] [numeric](24, 6) NULL,
	[q2_paid] [numeric](24, 6) NULL,

	[q3_total_cost] [numeric](24, 6) NULL,
	[q3_adjustments] [numeric](24, 6) NULL,
	[q3_available] [numeric](24, 6) NULL,
	[q3_reserved] [numeric](24, 6) NULL,
	[q3_allocated] [numeric](24, 6) NULL,
	[q3_paid] [numeric](24, 6) NULL,
	
	[q4_total_cost] [numeric](24, 6) NULL,
	[q4_adjustments] [numeric](24, 6) NULL,
	[q4_available] [numeric](24, 6) NULL,
	[q4_reserved] [numeric](24, 6) NULL,
	[q4_allocated] [numeric](24, 6) NULL,
	[q4_paid] [numeric](24, 6) NULL,
	
	[updatedAt] [datetime] NULL,
	[updatedBy] [nchar](30) NULL,
	[createdAt] [datetime] NULL,
	[createdBy] [nchar](30) NULL
) ON [PRIMARY];

CREATE TABLE [dbo].[R5_REF_COSTBASE_BUDGET_QUARTERLY](
	[id] [int] NOT NULL,
	[q1_total_cost] [numeric](24, 6) NULL,
	[q1_adjustments] [numeric](24, 6) NULL,
	[q1_available] [numeric](24, 6) NULL,
	[q1_reserved] [numeric](24, 6) NULL,
	[q1_allocated] [numeric](24, 6) NULL,
	[q1_paid] [numeric](24, 6) NULL,

	[q2_total_cost] [numeric](24, 6) NULL,
	[q2_adjustments] [numeric](24, 6) NULL,
	[q2_available] [numeric](24, 6) NULL,
	[q2_reserved] [numeric](24, 6) NULL,
	[q2_allocated] [numeric](24, 6) NULL,
	[q2_paid] [numeric](24, 6) NULL,

	[q3_total_cost] [numeric](24, 6) NULL,
	[q3_adjustments] [numeric](24, 6) NULL,
	[q3_available] [numeric](24, 6) NULL,
	[q3_reserved] [numeric](24, 6) NULL,
	[q3_allocated] [numeric](24, 6) NULL,
	[q3_paid] [numeric](24, 6) NULL,
	
	[q4_total_cost] [numeric](24, 6) NULL,
	[q4_adjustments] [numeric](24, 6) NULL,
	[q4_available] [numeric](24, 6) NULL,
	[q4_reserved] [numeric](24, 6) NULL,
	[q4_allocated] [numeric](24, 6) NULL,
	[q4_paid] [numeric](24, 6) NULL,
	
	[updatedAt] [datetime] NULL,
	[updatedBy] [nchar](30) NULL,
	[createdAt] [datetime] NULL,
	[createdBy] [nchar](30) NULL
) ON [PRIMARY];


ALTER VIEW [dbo].[R5_BUDGET_REALLOCATION_LOOKUP]
AS
SELECT    dbo.R5_DPP_VERSION.ORG_CODE, dbo.R5_DPP_VERSION.MRC_CODE, dbo.R5_DPP_VERSION.year_budget, dbo.R5_DPP_VERSION.version, 
                      dbo.R5_DPP_VERSION.reference_no, dbo.R5_EAM_DPP_ITEMBASE_BRIDGE.rowid, dbo.R5_EAM_DPP_ITEMBASE_LINES.code, dbo.R5PARTS.PAR_DESC, 
                      dbo.R5_DPP_VERSION.status, dbo.R5_EAM_DPP_ITEMBASE_LINES.available, dbo.R5_DPP_VERSION.cost_center,
					  dbo.R5_REF_ITEMBASE_BUDGET_QUARTERLY.q1_available, 
					  dbo.R5_REF_ITEMBASE_BUDGET_QUARTERLY.q2_available,
					  dbo.R5_REF_ITEMBASE_BUDGET_QUARTERLY.q3_available,
					  dbo.R5_REF_ITEMBASE_BUDGET_QUARTERLY.q4_available

FROM         dbo.R5_EAM_DPP_ITEMBASE_BRIDGE INNER JOIN
                      dbo.R5_DPP_VERSION ON dbo.R5_EAM_DPP_ITEMBASE_BRIDGE.reference_no = dbo.R5_DPP_VERSION.reference_no AND 
                      dbo.R5_EAM_DPP_ITEMBASE_BRIDGE.version = dbo.R5_DPP_VERSION.version INNER JOIN
                      dbo.R5_EAM_DPP_ITEMBASE_LINES ON dbo.R5_EAM_DPP_ITEMBASE_BRIDGE.rowid = dbo.R5_EAM_DPP_ITEMBASE_LINES.id INNER JOIN
                      dbo.R5PARTS ON dbo.R5_EAM_DPP_ITEMBASE_LINES.code COLLATE Latin1_General_BIN = dbo.R5PARTS.PAR_CODE INNER JOIN
					  dbo.R5_REF_ITEMBASE_BUDGET_QUARTERLY ON dbo.R5_EAM_DPP_ITEMBASE_LINES.record_id = dbo.R5_REF_ITEMBASE_BUDGET_QUARTERLY.id

GROUP BY dbo.R5_DPP_VERSION.ORG_CODE, dbo.R5_DPP_VERSION.MRC_CODE, dbo.R5_DPP_VERSION.year_budget, dbo.R5_DPP_VERSION.version, 
                      dbo.R5_DPP_VERSION.reference_no, dbo.R5_EAM_DPP_ITEMBASE_BRIDGE.rowid, dbo.R5_EAM_DPP_ITEMBASE_LINES.code, dbo.R5PARTS.PAR_DESC, 
                      dbo.R5_DPP_VERSION.status, dbo.R5_EAM_DPP_ITEMBASE_LINES.available, dbo.R5_DPP_VERSION.cost_center, 
					  dbo.R5_REF_ITEMBASE_BUDGET_QUARTERLY.q1_available, 
					  dbo.R5_REF_ITEMBASE_BUDGET_QUARTERLY.q2_available,
					  dbo.R5_REF_ITEMBASE_BUDGET_QUARTERLY.q3_available,
					  dbo.R5_REF_ITEMBASE_BUDGET_QUARTERLY.q4_available


ALTER VIEW [dbo].[R5_BUDGET_REALLOCATION_LOOKUP_COST]
AS
SELECT     dbo.R5_DPP_VERSION.ORG_CODE, dbo.R5_DPP_VERSION.MRC_CODE, dbo.R5_DPP_VERSION.year_budget, dbo.R5_DPP_VERSION.version, 
                      dbo.R5_DPP_VERSION.reference_no, dbo.R5_EAM_DPP_COSTBASE_BRIDGE.rowid, dbo.R5_EAM_DPP_COSTBASE_LINES.description, 
                      dbo.R5_DPP_VERSION.status, dbo.R5_EAM_DPP_COSTBASE_LINES.available, dbo.R5_DPP_VERSION.cost_center,
					  dbo.R5_REF_COSTBASE_BUDGET_QUARTERLY.q1_available, 
					  dbo.R5_REF_COSTBASE_BUDGET_QUARTERLY.q2_available,
					  dbo.R5_REF_COSTBASE_BUDGET_QUARTERLY.q3_available,
					  dbo.R5_REF_COSTBASE_BUDGET_QUARTERLY.q4_available
FROM         dbo.R5_EAM_DPP_COSTBASE_BRIDGE INNER JOIN
                      dbo.R5_DPP_VERSION ON dbo.R5_EAM_DPP_COSTBASE_BRIDGE.reference_no = dbo.R5_DPP_VERSION.reference_no AND 
                      dbo.R5_EAM_DPP_COSTBASE_BRIDGE.version = dbo.R5_DPP_VERSION.version INNER JOIN
                      dbo.R5_EAM_DPP_COSTBASE_LINES ON dbo.R5_EAM_DPP_COSTBASE_BRIDGE.rowid = dbo.R5_EAM_DPP_COSTBASE_LINES.id 
					  INNER JOIN dbo.R5_REF_COSTBASE_BUDGET_QUARTERLY ON dbo.R5_EAM_DPP_COSTBASE_LINES.record_id = dbo.R5_REF_COSTBASE_BUDGET_QUARTERLY.id

GROUP BY dbo.R5_DPP_VERSION.ORG_CODE, dbo.R5_DPP_VERSION.MRC_CODE, dbo.R5_DPP_VERSION.year_budget, dbo.R5_DPP_VERSION.version, 
                      dbo.R5_DPP_VERSION.reference_no, dbo.R5_EAM_DPP_COSTBASE_BRIDGE.rowid, 
                      dbo.R5_DPP_VERSION.status, dbo.R5_EAM_DPP_COSTBASE_LINES.available, dbo.R5_DPP_VERSION.cost_center, dbo.R5_EAM_DPP_COSTBASE_LINES.description,
					  dbo.R5_REF_COSTBASE_BUDGET_QUARTERLY.q1_available, 
					  dbo.R5_REF_COSTBASE_BUDGET_QUARTERLY.q2_available,
					  dbo.R5_REF_COSTBASE_BUDGET_QUARTERLY.q3_available,
					  dbo.R5_REF_COSTBASE_BUDGET_QUARTERLY.q4_available



					  
	  

GO


